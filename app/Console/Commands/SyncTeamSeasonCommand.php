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
        $teamSlug = $this->argument('teamSlug');
        $seasonInput = $this->argument('seasonNameOrId');

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

        if ($this->option('dry-run')) {
            $this->info('Spouštím DRY-RUN náhled synchronizace...');
            $results = $syncService->previewSync($team->id, $season->id);

            $this->table(['Kategorie', 'Počet / Informace'], [
                ['Soupiska - počet řádků', count($results['roster']['rows'] ?? [])],
                ['Zápasy - počet řádků', count($results['matches']['rows'] ?? [])],
                ['URL soupisky', $results['config']['team_season_url'] ?? 'N/A'],
                ['URL zápasů', $results['config']['matches_list_url'] ?? 'N/A'],
            ]);

            return self::SUCCESS;
        }

        if ($this->option('sync')) {
            $this->info('Spouštím synchronizaci synchronně...');

            // Vytvoření hlavního běhu pro UI/Progress
            $mainRun = \App\Models\ExternalImportRun::start(
                'czbasketball',
                $season->id,
                $team->id,
                $this->option('excesive') ? 'team_sync_excesive' : 'team_sync',
                null
            );
            $mainRun->update(['total_count' => 3]); // Roster, Matches, Details

            try {
                $mainRun->updateProgress(1, 3, 'Synchronizace soupisky');
                $syncService->syncTeamSeason($team->id, $season->id, array_merge($options, ['parent_run_id' => $mainRun->id]));
                $mainRun->finish(['status' => 'success']);
            } catch (\Exception $e) {
                $mainRun->fail($e);
                throw $e;
            }

            $this->info('Synchronizace dokončena.');
        } else {
            $this->info('Zařazuji synchronizaci do fronty (SyncTeamSeasonJob)...');
            SyncTeamSeasonJob::dispatch($team->id, $season->id, $options);
            $this->info('Úloha byla zařazena.');
        }

        return self::SUCCESS;
    }
}
