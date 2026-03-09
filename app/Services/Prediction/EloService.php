<?php

namespace App\Services\Prediction;

use App\Models\BasketballMatch;
use App\Models\TeamEloRating;
use App\Jobs\ComputeMatchPredictionJob;

class EloService
{
    public function __construct(
        protected EloCalculator $eloCalculator
    ) {}

    /**
     * Updates Elo ratings based on a finished match.
     */
    public function updateFromMatch(BasketballMatch $match): void
    {
        if (!$match->score_home || !$match->score_away) {
            return;
        }

        $seasonId = $match->season_id;
        $teamKey = TeamEloRating::getInternalTeamKey($match->team_id);
        $oppKey = TeamEloRating::getOpponentKey($match->opponent_id);

        $eloTeam = TeamEloRating::where('season_id', $seasonId)->where('team_key', $teamKey)->value('rating') ?? $this->eloCalculator::INITIAL_ELO;
        $eloOpp = TeamEloRating::where('season_id', $seasonId)->where('team_key', $oppKey)->value('rating') ?? $this->eloCalculator::INITIAL_ELO;

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

        TeamEloRating::updateOrCreate(
            ['season_id' => $seasonId, 'team_key' => $teamKey],
            ['rating' => $newRatings['new_elo_team'], 'last_match_at' => $match->scheduled_at]
        );

        TeamEloRating::updateOrCreate(
            ['season_id' => $seasonId, 'team_key' => $oppKey],
            ['rating' => $newRatings['new_elo_opponent'], 'last_match_at' => $match->scheduled_at]
        );

        // Re-predict future matches for these teams
        $this->dispatchFuturePredictions($match->team_id, $seasonId);
        $this->dispatchFuturePredictionsForOpponent($match->opponent_id, $seasonId);
    }

    private function dispatchFuturePredictions(int $teamId, int $seasonId): void
    {
        $futureMatches = BasketballMatch::where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->whereIn('status', ['planned', 'scheduled'])
            ->get();

        foreach ($futureMatches as $futureMatch) {
            ComputeMatchPredictionJob::dispatch($futureMatch->id);
        }
    }

    private function dispatchFuturePredictionsForOpponent(int $opponentId, int $seasonId): void
    {
        $futureMatches = BasketballMatch::where('opponent_id', $opponentId)
            ->where('season_id', $seasonId)
            ->whereIn('status', ['planned', 'scheduled'])
            ->get();

        foreach ($futureMatches as $futureMatch) {
            ComputeMatchPredictionJob::dispatch($futureMatch->id);
        }
    }
}
