<?php

namespace App\Console\Commands;

use App\Models\BasketballMatch;
use App\Models\TeamEloRating;
use App\Services\Prediction\EloService;
use Illuminate\Console\Command;

class RecomputeEloRatings extends Command
{
    protected $signature = 'stats:elo:recompute {season?} {team?}';

    protected $description = 'Recompute Elo ratings from historical matches';

    public function handle(EloService $eloService): int
    {
        $seasonId = $this->argument('season');
        $teamId = $this->argument('team');

        $matches = BasketballMatch::query();
        $ratings = TeamEloRating::query();

        if ($seasonId) {
            $matches->where('season_id', $seasonId);
            $ratings->where('season_id', $seasonId);
        }

        if ($teamId) {
            $matches->forHistoricalPredictionTeam((int) $teamId);
            $ratings->where('team_key', TeamEloRating::getInternalTeamKey((int) $teamId));
            $this->info('Team filter selects seasons; each selected season is rebuilt in full to preserve shared opponent histories.');
        }

        $seasonIds = $matches->distinct()->pluck('season_id')
            ->merge($ratings->distinct()->pluck('season_id'))
            ->unique()->sort()->values();

        if ($seasonIds->isEmpty()) {
            $this->info('No matches found to recompute Elo.');

            return self::SUCCESS;
        }

        $count = 0;
        foreach ($seasonIds as $selectedSeasonId) {
            $count += $eloService->rebuildSeason((int) $selectedSeasonId);
        }

        $this->info("Elo ratings recomputed successfully from {$count} completed matches.");

        return self::SUCCESS;
    }
}
