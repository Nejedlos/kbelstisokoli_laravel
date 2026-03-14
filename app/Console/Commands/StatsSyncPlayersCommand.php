<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Stats\Sync\PlayerSyncService;
use Illuminate\Console\Command;

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
                            {--excesive : Provést excesivní (hloubkovou) synchronizaci historie}';

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

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $successCount = 0;
        $currentIndex = 0;
        foreach ($users as $user) {
            // Kontrola, zda nebyl běh zrušen z UI
            if ($mainRun->refresh()->status === 'cancelled') {
                $this->warn('Synchronizace byla zrušena uživatelem.');
                break;
            }

            $currentIndex++;
            $mainRun->updateProgress($currentIndex, $users->count(), "Hráč: {$user->display_name}");

            $result = $syncService->syncPlayer($user, [
                'force' => $this->option('force'),
                'excesive' => $this->option('excesive'),
            ]);
            if ($result) {
                $successCount++;
            }
            $bar->advance();

            // Mikropauza mezi hráči, aby se ulevilo externímu webu
            if ($users->count() > 1) {
                usleep(300000); // 0.3s
            }
        }

        $bar->finish();
        $this->newLine();

        $mainRun->finish([
            'imported_count' => $successCount,
            'skipped_count' => $users->count() - $successCount,
        ]);

        $this->info("Synchronizace dokončena. Úspěšně: {$successCount}, Selhalo: " . ($users->count() - $successCount));

        return 0;
    }
}
