<?php

namespace App\Console\Commands;

use App\Services\Stats\Sync\ExternalStatsSyncService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class StatsImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:import
                            {--recent : Synchronizuje pouze nedávné zápasy pro všechny týmy}
                            {--force : Ignoruje hash a vynutí synchronizaci}
                            {--excesive : Spustí hloubkovou synchronizaci všech detailů zápasů}
                            {--queue : Zařadí synchronizaci do fronty místo spuštění v tomto procesu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spustí hromadnou synchronizaci externích statistik pro všechny povolené týmy.';

    /**
     * Execute the console command.
     */
    public function handle(ExternalStatsSyncService $syncService): int
    {
        if (! \Illuminate\Support\Facades\Config::get('external_sources.enabled')) {
            $this->warn('Synchronizace externích zdrojů je globálně zakázána v konfiguraci.');
            return self::FAILURE;
        }

        $activeSeason = \App\Models\Season::where('is_active', true)->first();
        if (! $activeSeason) {
            $this->error('Nebyla nalezena žádná aktivní sezóna.');
            return self::FAILURE;
        }

        $teamSlugs = \Illuminate\Support\Facades\Config::get('external_sources.czbasketball.teams', []);
        $teams = \App\Models\Team::whereIn('slug', $teamSlugs)->get();

        if ($teams->isEmpty()) {
            $this->warn('V konfiguraci nejsou definovány žádné týmy k synchronizaci.');
            return self::SUCCESS;
        }

        $recent = (bool) $this->option('recent');
        $force = (bool) $this->option('force');
        $excesive = (bool) $this->option('excesive');

        $options = [
            'recentOnly' => $recent,
            'force' => $force,
            'excesive' => $excesive,
        ];

        if ($recent) {
            $options['maxMatchDetails'] = \Illuminate\Support\Facades\Config::get('external_sources.czbasketball.limits.max_match_details_per_run', 10);
        }

        if ($this->option('queue')) {
            $this->info($recent
                ? 'Zařazuji prioritní synchronizaci nedávných zápasů (Recent) do fronty...'
                : 'Zařazuji celkovou synchronizaci sezóny pro všechny týmy (Baseline) do fronty...'
            );

            foreach ($teams as $team) {
                \App\Jobs\Stats\SyncTeamSeasonJob::dispatch($team->id, $activeSeason->id, $options);
            }

            $this->info('Všechny úlohy byly zařazeny do fronty.');
            return self::SUCCESS;
        }

        $this->info($recent
            ? 'Spouštím prioritní synchronizaci nedávných zápasů (Recent) pro všechny týmy...'
            : 'Spouštím celkovou synchronizaci sezóny pro všechny týmy (Baseline)...'
        );

        $totalWork = $teams->count();
        $this->info("Zahajuji synchronizaci pro {$totalWork} týmů v sezóně {$activeSeason->name}.");

        // Vytvoření hlavního běhu pro UI/Progress
        $mainRun = \App\Models\ExternalImportRun::start(
            'czbasketball',
            $activeSeason->id,
            null,
            $recent ? 'batch_recent' : ($excesive ? 'batch_baseline_excesive' : 'batch_baseline'),
            null
        );
        $mainRun->update(['total_count' => $totalWork]);

        // Podpora pro signály (zrušení přes Ctrl+C)
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
        }

        // Sekce pro progress bar a logování (pokud jsou podporovány)
        $output = $this->getOutput()->getOutput();
        $barSection = method_exists($output, 'section') ? $output->section() : null;
        $logSection = method_exists($output, 'section') ? $output->section() : $output;

        $bar = new ProgressBar($barSection ?: $this->output, $totalWork);

        $bar->start();

        $count = 0;
        try {
            foreach ($teams as $team) {
                // Kontrola, zda nebyl běh zrušen z UI
                if ($mainRun->refresh()->status === 'cancelled') {
                    $logSection->writeln('<fg=yellow>Synchronizace byla zrušena uživatelem.</>');
                    break;
                }

                $count++;
                $mainRun->updateProgress($count, $totalWork, "Tým: {$team->name}");

                $syncService->syncTeamSeason($team->id, $activeSeason->id, array_merge($options, ['parent_run_id' => $mainRun->id]));

                $bar->advance();

                // Mikropauza mezi týmy, abychom nehltili externí web
                if ($totalWork > 1) {
                    usleep(1000000); // 1.0s
                }
            }
            $bar->finish();
            if ($barSection) {
                $barSection->clear();
            }
            $mainRun->finish(['status' => 'success']);
        } catch (\Exception $e) {
            $bar->finish();
            $mainRun->fail($e);
            throw $e;
        }

        $this->info('Synchronizace všech týmů dokončena.');

        return self::SUCCESS;
    }
}
