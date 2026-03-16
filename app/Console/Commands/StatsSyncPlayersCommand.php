<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Stats\Sync\PlayerSyncService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class StatsSyncPlayersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:sync-players {--user_id= : Synchronizovat pouze konkrétního uživatele}
                            {--team_id= : Synchronizovat pouze hráče z daného týmu}
                            {--force : Vynutit synchronizaci i bez změn}
                            {--excesive : Provést excesivní (hloubkovou) synchronizaci historie}
                            {--limit= : Omezit počet synchronizovaných hráčů (např. 10)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizuje detaily hráčů (fotky, historii) z externích zdrojů (cz.basketball)';

    /**
     * Execute the console command.
     */
    public function handle(PlayerSyncService $syncService)
    {
        set_time_limit(0); // Pro hromadnou synchronizaci historie vypneme časový limit procesu
        $this->info('Zahajuji synchronizaci detailů hráčů...');

        $query = User::query();

        // Filtrujeme pouze ty, kteří mají externí mapování na czbasketball
        $query->whereHas('externalMappings', function ($q) {
            $q->where('source_key', 'czbasketball');
        });

        if ($userId = $this->option('user_id')) {
            $query->where('id', $userId);
        }

        if ($teamId = $this->option('team_id')) {
            $query->whereHas('playerProfiles.teams', function ($q) use ($teamId) {
                $q->where('teams.id', $teamId);
            });
        }

        if ($limit = $this->option('limit')) {
            $query->limit($limit);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('Nebyli nalezeni žádní hráči s externím mapováním.');
            return 0;
        }

        $this->info("Nalezeno {$users->count()} hráčů k synchronizaci.");

        // Vytvoření hlavního běhu pro UI
        $mainRun = \App\Models\ExternalImportRun::start(
            'czbasketball',
            \App\Models\Season::where('is_active', true)->first()?->id ?? 0,
            $this->option('team_id'),
            $this->option('excesive') ? 'player_sync_excesive' : 'player_sync_batch',
            null
        );
        $mainRun->update(['total_count' => $users->count()]);

        // Podpora pro signály
        if (function_exists('pcntl_signal')) {
            declare(ticks=1);
            pcntl_signal(SIGINT, function () use ($mainRun) {
                $mainRun->cancel('Zrušeno signálem SIGINT (Ctrl+C)');
                exit;
            });
            pcntl_signal(SIGTERM, function () use ($mainRun) {
                $mainRun->cancel('Zrušeno signálem SIGTERM');
                exit;
            });

            // Handler pro timeout (3 minuty na hráče)
            pcntl_signal(SIGALRM, function () {
                throw new \RuntimeException('Player sync timeout exceeded (3 minutes)');
            });
        }

        // Sekce pro progress bar a logování (pokud jsou podporovány)
        $output = $this->getOutput()->getOutput();
        $barSection = method_exists($output, 'section') ? $output->section() : null;
        $logSection = method_exists($output, 'section') ? $output->section() : $output;

        $bar = new ProgressBar($barSection ?: $this->output, $users->count());

        $bar->start();

        $successCount = 0;
        $skippedCount = 0;
        $currentIndex = 0;
        foreach ($users as $user) {
            // Kontrola, zda nebyl běh zrušen z UI
            if ($mainRun->refresh()->status === 'cancelled') {
                $logSection->writeln('<fg=yellow>Synchronizace byla zrušena uživatelem.</>');
                break;
            }

            $currentIndex++;
            $mainRun->updateProgress($currentIndex, $users->count(), "Hráč: {$user->display_name}");

            // Nastavení alarmu na 3 minuty
            if (function_exists('pcntl_alarm')) {
                pcntl_alarm(180);
            }

            try {
                $result = $syncService->syncPlayer($user, [
                    'force' => $this->option('force'),
                    'excesive' => $this->option('excesive'),
                    'parent_run' => $mainRun,
                    'current_index' => $currentIndex,
                    'total_count' => $users->count(),
                ]);

                if ($result === 1) {
                    $successCount++;
                } elseif ($result === 2) {
                    $skippedCount++;
                }
            } catch (\Throwable $e) {
                $errorMessage = $e->getMessage();
                $isTimeout = str_contains($errorMessage, 'timeout exceeded');

                if ($isTimeout) {
                    $logSection->writeln("<fg=red>Timeout u hráče {$user->display_name} (překročeny 3 minuty). Přeskakuji.</>");
                    \Log::error("StatsSyncPlayersCommand: Timeout for player {$user->display_name} (ID: {$user->id})");
                } else {
                    $logSection->writeln("<fg=red>Chyba u hráče {$user->display_name}: {$errorMessage}</>");
                    \Log::error("StatsSyncPlayersCommand: Error for player {$user->display_name} (ID: {$user->id}): {$errorMessage}");
                }

                // Pokud to nebyl timeout, možná budeme chtít počítat jako selhání
                // Prozatím to necháme v "failed_count", který se dopočítává na konci
            } finally {
                // Zrušení alarmu
                if (function_exists('pcntl_alarm')) {
                    pcntl_alarm(0);
                }
            }

            $bar->advance();

            // Refresh po každém hráči
            $this->refreshState();
        }

        $bar->finish();
        if ($barSection) {
            $barSection->clear(); // Volitelně vyčistit sekci baru po dokončení, nebo nechat
        }

        $mainRun->finish([
            'imported_count' => $successCount,
            'skipped_count' => $skippedCount,
            'failed_count' => $users->count() - $successCount - $skippedCount,
        ]);

        $this->info("Synchronizace dokončena.");
        $this->line("<fg=green>Úspěšně: {$successCount}</>");
        $this->line("<fg=yellow>Přeskočeno: {$skippedCount}</>");
        $this->line("<fg=red>Selhalo: " . ($users->count() - $successCount - $skippedCount) . "</>");

        return 0;
    }
    /**
     * Vyčistí stav aplikace, aby se ušetřila paměť a předešlo se "zaseknutí".
     */
    protected function refreshState(): void
    {
        // Uvolnění cyklických odkazů v PHP
        gc_collect_cycles();

        // Vyčištění DB query logu (pokud je zapnutý, může požírat stovky MB)
        \DB::connection()->flushQueryLog();
        \DB::connection()->disableQueryLog();

        // Mikropauza mezi hráči, aby se ulevilo externímu webu
        usleep(300000); // 0.3s
    }
}
