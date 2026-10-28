<?php

namespace App\Services\Prediction;

use App\Jobs\ComputeMatchPredictionJob;
use App\Models\BasketballMatch;
use App\Models\Season;
use App\Models\TeamEloRating;
use Illuminate\Support\Facades\DB;

class EloService
{
    public function __construct(
        protected EloCalculator $eloCalculator
    ) {}

    /**
     * Replay rather than apply a result twice when a score or status changes.
     * This also removes the contribution of a deleted or no longer finished match.
     */
    public function updateFromMatch(BasketballMatch $match): void
    {
        if ($match->season_id === null) {
            return;
        }

        $this->rebuildSeason((int) $match->season_id);
    }

    /**
     * Rebuild the entire season because opponents can be shared by several teams.
     *
     * @return int Number of completed results included in the replay.
     */
    public function rebuildSeason(int $seasonId): int
    {
        return DB::transaction(function () use ($seasonId): int {
            // A stable row serializes rebuilds even when the season has no ratings yet.
            if (! Season::whereKey($seasonId)->lockForUpdate()->first()) {
                return 0;
            }

            $matches = BasketballMatch::where('season_id', $seasonId)
                ->whereNotNull('team_id')
                ->whereNotNull('opponent_id')
                ->whereNotNull('scheduled_at')
                ->whereIn('status', ['finished', 'played', 'completed'])
                ->whereNotNull('score_home')
                ->whereNotNull('score_away')
                ->where('score_home', '>=', 0)
                ->where('score_away', '>=', 0)
                ->where(function ($query): void {
                    $query->where('score_home', '>', 0)->orWhere('score_away', '>', 0);
                })
                ->orderBy('scheduled_at')
                ->orderBy('id')
                ->get();

            $ratings = [];
            $gamesPlayed = [];
            $lastMatches = [];

            foreach ($matches as $match) {
                $teamKey = TeamEloRating::getInternalTeamKey($match->team_id);
                $oppKey = TeamEloRating::getOpponentKey($match->opponent_id);
                $scoreDiff = $match->our_score - $match->opponent_score;
                $outcome = $scoreDiff > 0 ? 1.0 : ($scoreDiff < 0 ? 0.0 : 0.5);

                $newRatings = $this->eloCalculator->calculateNewRatings(
                    $ratings[$teamKey] ?? EloCalculator::INITIAL_ELO,
                    $ratings[$oppKey] ?? EloCalculator::INITIAL_ELO,
                    $outcome,
                    $scoreDiff,
                    $match->is_home,
                    $gamesPlayed[$teamKey] ?? 0
                );

                $ratings[$teamKey] = $newRatings['new_elo_team'];
                $ratings[$oppKey] = $newRatings['new_elo_opponent'];
                $gamesPlayed[$teamKey] = ($gamesPlayed[$teamKey] ?? 0) + 1;
                $lastMatches[$teamKey] = $lastMatches[$oppKey] = $match->scheduled_at;
            }

            // Replacing the snapshot also clears teams whose final result was removed.
            TeamEloRating::where('season_id', $seasonId)->delete();

            foreach ($ratings as $teamKey => $rating) {
                TeamEloRating::create([
                    'season_id' => $seasonId,
                    'team_key' => $teamKey,
                    'rating' => $rating,
                    'last_match_at' => $lastMatches[$teamKey],
                ]);
            }

            DB::afterCommit(fn () => $this->dispatchFuturePredictions($seasonId));

            return $matches->count();
        });
    }

    private function dispatchFuturePredictions(int $seasonId): void
    {
        // A shared opponent can propagate a correction to any team in the season.
        BasketballMatch::where('season_id', $seasonId)
            ->whereNotNull('team_id')
            ->whereNotNull('opponent_id')
            ->whereIn('status', ['planned', 'scheduled'])
            ->select('id')
            ->eachById(function (BasketballMatch $match): void {
                ComputeMatchPredictionJob::dispatch($match->id);
            });
    }
}
