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
                            {--sync : Spustí synchronizaci synchronně (v tomto procesu)}';

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
            ->where('metadata->external->season_external_match_id', $matchExternalId)
            ->first();

        $this->info('Synchronizuji zápas: '.($match ? "{$match->scheduled_at->toDateString()} vs {$match->opponent?->name}" : $matchExternalId));

        $options = [
            'force' => $this->option('force'),
        ];

        if ($this->option('sync')) {
            $this->info('Spouštím synchronizaci synchronně...');
            // syncMatchDetail očekává interní matchId, ale my ho možná ještě nemáme v DB
            // nebo chceme jen synchronizovat podle externího ID.
            // Služba syncMatchDetail v ExternalStatsSyncService bere matchId.
            // Ale my můžeme chtít volat StatSyncService přímo nebo upravit ExternalStatsSyncService.

            // Pokud match neexistuje, musíme ho nejdřív najít v seznamu zápasů.
            // Ale z CLI obvykle synchronizujeme už existující nebo známý zápas.

            if (! $match) {
                $this->warn("Zápas s externím ID {$matchExternalId} nebyl nalezen v interní DB. Zkuste nejdříve sync-team-season.");

                return self::FAILURE;
            }

            $syncService->syncMatchDetail($match->id);
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
