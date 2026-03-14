<?php

namespace App\Console\Commands;

use App\Jobs\Stats\SyncTeamSeasonJob;
use App\Models\Season;
use App\Models\Team;
use App\Services\Stats\Sync\ExternalStatsSyncService;
use Illuminate\Console\Command;

class SyncTeamSeasonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:sync-team-season
                            {teamSlug : Slug týmu (např. muzi-c)}
                            {seasonNameOrId : Název sezóny (2024/2025) nebo její ID}
                            {--dry-run : Spustí pouze náhled bez zápisu do DB}
                            {--force : Ignoruje hash a vynutí synchronizaci}
                            {--fresh : Smaže stávající data před novým importem}
                            {--ai : Použije AI pro normalizaci}
                            {--excesive : Spustí hloubkovou synchronizaci všech detailů zápasů}
                            {--max-matches=20 : Maximální počet detailů zápasů ke stažení}
                            {--recent-days=3 : Počet dní zpět pro prioritní synchronizaci}
                            {--sync : Spustí synchronizaci synchronně (v tomto procesu)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Spustí synchronizaci externích statistik pro konkrétní tým a sezónu.';

    /**
     * Execute the console command.
     */
    public function handle(ExternalStatsSyncService $syncService)
    {
        $teamSlugInput = $this->argument('teamSlug');
        $seasonInput = $this->argument('seasonNameOrId');

        // Rozhodnutí o batchi
        $teams = [];
        if ($teamSlugInput === 'all') {
            $teams = Team::all();
        } else {
            $team = Team::where('slug', $teamSlugInput)->first();
            if ($team) {
                $teams = [$team];
            } else {
                $this->error("Tým se slugem '{$teamSlugInput}' nebyl nalezen.");
                return self::FAILURE;
            }
        }

        $seasons = [];
        if ($seasonInput === 'all') {
            $seasons = Season::orderBy('name', 'desc')->get();
        } else {
            $season = is_numeric($seasonInput)
                ? Season::find($seasonInput)
                : Season::where('name', $seasonInput)->first();

            if ($season) {
                $seasons = [$season];
            } else {
                $this->error("Sezóna '{$seasonInput}' nebyla nalezena.");
                return self::FAILURE;
            }
        }

        $options = [
            'dryRun' => $this->option('dry-run'),
            'force' => $this->option('force'),
            'fresh' => $this->option('fresh'),
            'ai' => $this->option('ai'),
            'excesive' => $this->option('excesive'),
            'maxMatchDetails' => (int) $this->option('max-matches'),
            'recentOnly' => (bool) $this->option('recent-days'),
            'recentDays' => (int) $this->option('recent-days'),
        ];

        $totalWork = count($teams) * count($seasons);
        $this->info("Zahajuji synchronizaci pro {$totalWork} kombinací tým/sezóna.");

        if ($this->option('dry-run')) {
            $this->info('Spouštím DRY-RUN náhled synchronizace (pouze první kombinace)...');
            if ($totalWork > 0) {
                $results = $syncService->previewSync($teams[0]->id, $seasons[0]->id);
                $this->table(['Kategorie', 'Počet / Informace'], [
                    ['Soupiska - počet řádků', count($results['roster']['rows'] ?? [])],
                    ['Zápasy - počet řádků', count($results['matches']['rows'] ?? [])],
                    ['URL soupisky', $results['config']['team_season_url'] ?? 'N/A'],
                    ['URL zápasů', $results['config']['matches_list_url'] ?? 'N/A'],
                ]);
            }
            return self::SUCCESS;
        }

        if ($this->option('sync')) {
            $this->info('Spouštím synchronizaci synchronně...');

            // Vytvoření hlavního běhu pro UI/Progress
            $mainRun = \App\Models\ExternalImportRun::start(
                'czbasketball',
                $seasons[0]->id ?? 0,
                $teams[0]->id ?? 0,
                $this->option('excesive') ? 'team_sync_excesive' : 'team_sync',
                null
            );
            $mainRun->update(['total_count' => $totalWork]);

            $count = 0;
            try {
                foreach ($teams as $team) {
                    foreach ($seasons as $season) {
                        $count++;
                        $this->info("Synchronizuji: {$team->name} | {$season->name} ({$count}/{$totalWork})");
                        $mainRun->updateProgress($count, $totalWork, "Tým: {$team->name} ({$season->name})");

                        $syncService->syncTeamSeason($team->id, $season->id, array_merge($options, ['parent_run_id' => $mainRun->id]));
                        $mainRun->increment('imported_count');

                        // Mikropauza mezi týmy/sezónami, abychom nehltili externí web
                        if ($totalWork > 1) {
                            $delay = $totalWork > 10 ? 1500000 : 500000; // 1.5s pro velké dávky, jinak 0.5s
                            usleep($delay);
                        }
                    }
                }
                $mainRun->finish(['status' => 'success']);
            } catch (\Exception $e) {
                $mainRun->fail($e);
                throw $e;
            }

            $this->info('Synchronizace dokončena.');
        } else {
            $this->info('Zařazuji synchronizaci do fronty (po jednotlivých úlohách)...');
            foreach ($teams as $team) {
                foreach ($seasons as $season) {
                    SyncTeamSeasonJob::dispatch($team->id, $season->id, $options);
                }
            }
            $this->info('Všechny úlohy byly zařazeny.');
        }

        return self::SUCCESS;
    }
}
