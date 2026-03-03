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
                            {teamSlug : Slug týmu (např. muzi-c)}
                            {seasonNameOrId : Název sezóny (2024/2025) nebo její ID}';

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

        $team = Team::where('slug', $teamSlug)->first();
        if (!$team) {
            $this->error("Tým se slugem '{$teamSlug}' nebyl nalezen.");
            return self::FAILURE;
        }

        $season = is_numeric($seasonInput)
            ? Season::find($seasonInput)
            : Season::where('name', $seasonInput)->first();

        if (!$season) {
            $this->error("Sezóna '{$seasonInput}' nebyla nalezena.");
            return self::FAILURE;
        }

        $this->info("Zahajuji přepočet statistik pro tým {$team->name} a sezónu {$season->name}...");

        $this->info("1/2 Přepočítávám souhrny hráčů (pro celou sezónu)...");
        $statService->recomputePlayerSummaries($season->id);

        $this->info("2/2 Přepočítávám souhrn týmu...");
        $statService->recomputeTeamSummary($season->id, $team->id);

        $this->info("Přepočet dokončen úspěšně.");

        return self::SUCCESS;
    }
}
