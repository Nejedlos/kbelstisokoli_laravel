<?php

namespace App\Console\Commands;

use App\Jobs\Stats\SyncMatchDetailJob;
use App\Models\BasketballMatch;
use App\Models\Season;
use App\Models\Team;
use App\Services\Stats\Sync\ExternalStatsSyncService;
use Illuminate\Console\Command;

class SyncMatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:sync-match
                            {matchExternalId : Externí ID zápasu (z URL /zapas/{id})}
                            {seasonNameOrId : Název sezóny (2024/2025) nebo její ID}
                            {teamSlug : Slug týmu (např. muzi-c)}
                            {--force : Ignoruje hash a vynutí synchronizaci}
                            {--fresh : Smaže stávající data před novým importem}
                            {--queue : Zařadí synchronizaci do fronty místo spuštění v tomto procesu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizuje detaily (boxscore) konkrétního zápasu z externího zdroje.';

    /**
     * Execute the console command.
     */
    public function handle(ExternalStatsSyncService $syncService)
    {
        $matchExternalId = $this->argument('matchExternalId');
        $seasonInput = $this->argument('seasonNameOrId');
        $teamSlug = $this->argument('teamSlug');

        $team = Team::where('slug', $teamSlug)->first();
        if (! $team) {
            $this->error("Tým se slugem '{$teamSlug}' nebyl nalezen.");

            return self::FAILURE;
        }

        $season = is_numeric($seasonInput)
            ? Season::find($seasonInput)
            : Season::where('name', $seasonInput)->first();

        if (! $season) {
            $this->error("Sezóna '{$seasonInput}' nebyla nalezena.");

            return self::FAILURE;
        }

        // Najdeme interní zápas pokud existuje pro lepší výpis
        $match = BasketballMatch::where('team_id', $team->id)
            ->where('season_id', $season->id)
            ->where(function ($q) use ($matchExternalId) {
                $q->where('metadata', 'LIKE', '%"external_id":"' . $matchExternalId . '"%')
                  ->orWhere('metadata', 'LIKE', '%"season_external_match_id":"' . $matchExternalId . '"%');
            })
            ->first();

        $this->info('Synchronizuji zápas: '.($match ? "{$match->scheduled_at->toDateString()} vs {$match->opponent?->name}" : $matchExternalId));

        $options = [
            'force' => $this->option('force'),
            'fresh' => $this->option('fresh'),
        ];

        if (! $this->option('queue')) {
            $this->info('Spouštím synchronizaci...');

            if (! $match) {
                $this->warn("Zápas s externím ID {$matchExternalId} nebyl nalezen v interní DB. Zkuste nejdříve sync-team-season.");

                return self::FAILURE;
            }

            // Sekce pro progress bar a logování (pokud jsou podporovány)
            $output = $this->getOutput()->getOutput();
            $barSection = method_exists($output, 'section') ? $output->section() : null;
            $logSection = method_exists($output, 'section') ? $output->section() : $output;

            $bar = $barSection
                ? $barSection->createProgressBar(1)
                : $this->output->createProgressBar(1);

            $bar->start();

            // Vytvoření běhu pro UI/Progress
            $run = \App\Models\ExternalImportRun::start(
                'czbasketball',
                $season->id,
                $team->id,
                'match_detail_command',
                $matchExternalId
            );
            $run->update(['total_count' => 1]);
            $run->updateProgress(0, 1, "Zápas: " . ($match ? "{$match->scheduled_at->toDateString()} vs {$match->opponent?->name}" : $matchExternalId));

            // Podpora pro signály (zrušení přes Ctrl+C)
            if (function_exists('pcntl_signal')) {
                declare(ticks=1);
                pcntl_signal(SIGINT, function () use ($run) {
                    $run->cancel('Zrušeno signálem SIGINT (Ctrl+C)');
                    exit;
                });
                pcntl_signal(SIGTERM, function () use ($run) {
                    $run->cancel('Zrušeno signálem SIGTERM');
                    exit;
                });
            }

            try {
                $syncService->syncMatchDetail($match->id, array_merge($options, ['parent_run_id' => $run->id]));
                $bar->advance();
                $run->finish(['imported_count' => 1]);
            } catch (\Exception $e) {
                $bar->finish();
                $run->fail($e);
                throw $e;
            }

            $bar->finish();
            if ($barSection) {
                $barSection->clear();
            }
            $this->info('Synchronizace zápasu dokončena.');
        } else {
            $this->info('Zařazuji synchronizaci do fronty (SyncMatchDetailJob)...');
            if (! $match) {
                $this->error('Zápas nebyl nalezen v DB. SyncMatchDetailJob vyžaduje existující ID.');

                return self::FAILURE;
            }
            SyncMatchDetailJob::dispatch($match->id, $team->id, $season->id, $matchExternalId, $options);
            $this->info('Úloha byla zařazena.');
        }

        return self::SUCCESS;
    }
}
