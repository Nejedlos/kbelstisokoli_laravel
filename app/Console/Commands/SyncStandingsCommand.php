<?php

namespace App\Console\Commands;

use App\Models\ExternalTeamSeasonConfig;
use App\Models\Season;
use App\Services\Stats\Sync\CompetitionSyncService;
use Illuminate\Console\Command;

class SyncStandingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:sync-standings
                            {seasonNameOrId? : Název sezóny (2024/2025) nebo její ID (výchozí aktivní)}
                            {--force : Ignoruje hash a vynutí synchronizaci}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronizuje tabulky (standings) ze všech nakonfigurovaných soutěží.';

    /**
     * Execute the console command.
     */
    public function handle(CompetitionSyncService $syncService)
    {
        $seasonInput = $this->argument('seasonNameOrId');

        if ($seasonInput === 'all') {
            $seasons = Season::orderBy('name', 'desc')->get();
            \App\Services\Support\ConsoleService::log("Zahajuji synchronizaci tabulek pro VŠECHNY sezóny (" . $seasons->count() . ").", 'info');

            $totalSynced = 0;
            $totalUrls = 0;

            foreach ($seasons as $season) {
                $result = $syncService->syncAllStandings($season);
                $totalSynced += $result['synced'];
                $totalUrls += $result['total'];
            }

            \App\Services\Support\ConsoleService::log("KOMPLETNÍ SYNCHRONIZACE DOKONČENA.", 'success');
            \App\Services\Support\ConsoleService::log("Celkem sezón: " . $seasons->count(), 'info');
            \App\Services\Support\ConsoleService::log("Celkem soutěží: {$totalSynced}/{$totalUrls}", 'success');

            return self::SUCCESS;
        }

        if (!$seasonInput) {
            $season = Season::where('is_active', true)->first() ?? Season::latest('id')->first();
        } else {
            $season = is_numeric($seasonInput)
                ? Season::find($seasonInput)
                : Season::where('name', $seasonInput)->first();
        }

        if (!$season) {
            $this->error("Sezóna nebyla nalezena.");
            return self::FAILURE;
        }

        $this->syncForSeason($syncService, $season);

        return self::SUCCESS;
    }

    protected function syncForSeason(CompetitionSyncService $syncService, Season $season): void
    {
        $syncService->syncAllStandings($season);
    }
}
