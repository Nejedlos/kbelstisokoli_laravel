<?php

namespace App\Console\Commands;

use App\Models\BasketballMatch;
use App\Models\TeamEloRating;
use App\Services\Prediction\EloCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecomputeEloRatings extends Command
{
    protected $signature = 'stats:elo:recompute {season?} {team?}';
    protected $description = 'Recompute Elo ratings from historical matches';

    private EloCalculator $eloCalculator;

    public function __construct(EloCalculator $eloCalculator)
    {
        parent::__construct();
        $this->eloCalculator = $eloCalculator;
    }

    public function handle(): int
    {
        $seasonId = $this->argument('season');
        $teamId = $this->argument('team');

        $query = BasketballMatch::whereNotNull('score_home')
            ->whereNotNull('score_away')
            ->orderBy('scheduled_at', 'asc');

        if ($seasonId) {
            $query->where('season_id', $seasonId);
        }

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        $matches = $query->get();

        if ($matches->isEmpty()) {
            $this->info('No matches found to recompute Elo.');
            return 0;
        }

        // Reset Elo ratings if we are recomputing everything or a specific season
        if (!$teamId) {
            $eloQuery = TeamEloRating::query();
            if ($seasonId) {
                $eloQuery->where('season_id', $seasonId);
            }
            $eloQuery->delete();
        }

        $this->info("Recomputing Elo for {$matches->count()} matches...");

        $eloRatings = []; // [season_id][team_key] => rating

        foreach ($matches as $match) {
            $seasonId = $match->season_id;
            $teamKey = TeamEloRating::getInternalTeamKey($match->team_id);
            $oppKey = TeamEloRating::getOpponentKey($match->opponent_id);

            if (!isset($eloRatings[$seasonId][$teamKey])) {
                $eloRatings[$seasonId][$teamKey] = TeamEloRating::where('season_id', $seasonId)->where('team_key', $teamKey)->value('rating') ?? $this->eloCalculator::INITIAL_ELO;
            }
            if (!isset($eloRatings[$seasonId][$oppKey])) {
                $eloRatings[$seasonId][$oppKey] = TeamEloRating::where('season_id', $seasonId)->where('team_key', $oppKey)->value('rating') ?? $this->eloCalculator::INITIAL_ELO;
            }

            $eloTeam = $eloRatings[$seasonId][$teamKey];
            $eloOpp = $eloRatings[$seasonId][$oppKey];

            $outcome = 0;
            $scoreDiff = 0;
            if ($match->is_home) {
                if ($match->score_home > $match->score_away) $outcome = 1;
                elseif ($match->score_home < $match->score_away) $outcome = 0;
                else $outcome = 0.5;
                $scoreDiff = $match->score_home - $match->score_away;
            } else {
                if ($match->score_away > $match->score_home) $outcome = 1;
                elseif ($match->score_away < $match->score_home) $outcome = 0;
                else $outcome = 0.5;
                $scoreDiff = $match->score_away - $match->score_home;
            }

            $newRatings = $this->eloCalculator->calculateNewRatings(
                $eloTeam,
                $eloOpp,
                $outcome,
                $scoreDiff,
                $match->is_home
            );

            $eloRatings[$seasonId][$teamKey] = $newRatings['new_elo_team'];
            $eloRatings[$seasonId][$oppKey] = $newRatings['new_elo_opponent'];

            TeamEloRating::updateOrCreate(
                ['season_id' => $seasonId, 'team_key' => $teamKey],
                ['rating' => $newRatings['new_elo_team'], 'last_match_at' => $match->scheduled_at]
            );

            TeamEloRating::updateOrCreate(
                ['season_id' => $seasonId, 'team_key' => $oppKey],
                ['rating' => $newRatings['new_elo_opponent'], 'last_match_at' => $match->scheduled_at]
            );
        }

        $this->info('Elo ratings recomputed successfully.');

        return 0;
    }
}
