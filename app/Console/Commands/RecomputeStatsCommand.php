<?php

namespace App\Console\Commands;

use App\Models\Season;
use App\Models\Team;
use App\Services\Stats\Sync\StatisticSyncService;
use Illuminate\Console\Command;

class RecomputeStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:recompute
                            {teamSlug? : Slug týmu (např. muzi-c), pokud není zadán, přepočítá všechny aktivní týmy}
                            {seasonNameOrId? : Název sezóny (2024/2025) nebo její ID, pokud není zadána, použije aktivní sezónu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Přepočítá sezónní souhrny pro tým a všechny hráče v dané sezóně.';

    /**
     * Execute the console command.
     */
    public function handle(StatisticSyncService $statService)
    {
        $teamSlug = $this->argument('teamSlug');
        $seasonInput = $this->argument('seasonNameOrId');

        // Určení sezóny
        $season = null;
        if ($seasonInput) {
            $season = is_numeric($seasonInput)
                ? Season::find($seasonInput)
                : Season::where('name', $seasonInput)->first();

            if (! $season) {
                $this->error("Sezóna '{$seasonInput}' nebyla nalezena.");

                return self::FAILURE;
            }
        } else {
            $season = Season::where('is_active', true)->first();
            if (! $season) {
                $this->error('Aktivní sezóna nebyla nalezena a nebyla zadána žádná konkrétní.');

                return self::FAILURE;
            }
        }

        // Určení týmů
        $teams = collect();
        if ($teamSlug) {
            $team = Team::where('slug', $teamSlug)->first();
            if (! $team) {
                $this->error("Tým se slugem '{$teamSlug}' nebyl nalezen.");

                return self::FAILURE;
            }
            $teams->push($team);
        } else {
            $teamSlugs = config('external_sources.czbasketball.teams', []);
            $teams = Team::whereIn('slug', $teamSlugs)->get();

            if ($teams->isEmpty()) {
                $this->warn('V konfiguraci nejsou definovány žádné sledované týmy k přepočtu.');
            }
        }

        $this->info("Zahajuji přepočet statistik pro sezónu {$season->name}...");

        // 1. Přepočet hráčů (globálně pro celou sezónu)
        $this->info('1/2 Přepočítávám souhrny hráčů (pro celou sezónu)...');
        $statService->recomputePlayerSummaries($season->id);

        // 2. Přepočet týmů
        $this->info("2/2 Přepočítávám souhrny pro " . $teams->count() . " týmů...");
        foreach ($teams as $team) {
            $this->info("- {$team->name}");
            $statService->recomputeTeamSummary($season->id, $team->id);
        }

        $this->info('Přepočet dokončen úspěšně.');

        return self::SUCCESS;
    }
}
