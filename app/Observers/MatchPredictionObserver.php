<?php

namespace App\Observers;

use App\Jobs\ComputeMatchPredictionJob;
use App\Models\BasketballMatch;
use App\Models\StatisticRow;
use App\Services\Prediction\EloService;
use App\Services\Prediction\PredictionService;

class MatchPredictionObserver
{
    public function __construct(
        protected EloService $eloService,
        protected PredictionService $predictionService
    ) {}

    public function saved($model): void
    {
        if ($model instanceof BasketballMatch) {
            if ($model->wasChanged(['team_id', 'opponent_id', 'season_id', 'is_home']) || ! $model->hasPredictionContext()) {
                $model->prediction()->delete();
                $model->unsetRelation('prediction');
            }

            // Jen pro budoucí nebo čerstvě odehrané zápasy
            if ($model->hasPredictionContext() && in_array($model->status, ['planned', 'scheduled'])) {
                ComputeMatchPredictionJob::dispatch($model->id)->afterCommit();
            }

            // Pokud se změnil výsledek u odehraného zápasu, přepočítáme Elo
            if (($this->hasCompletedResult($model->getAttributes()) || $this->hasCompletedResult($model->getRawOriginal()))
                && ($model->wasRecentlyCreated || $model->wasChanged(['score_home', 'score_away', 'status', 'team_id', 'opponent_id', 'season_id', 'scheduled_at', 'is_home']))) {
                if ($model->wasChanged('season_id') && $model->getRawOriginal('season_id')) {
                    $this->eloService->rebuildSeason((int) $model->getRawOriginal('season_id'));
                }
                $this->eloService->updateFromMatch($model);
            }
        }

        if ($model instanceof StatisticRow) {
            $this->predictionService->queueForStatistics($model);
        }
    }

    public function deleted($model): void
    {
        if ($model instanceof BasketballMatch && $this->hasCompletedResult($model->getAttributes())) {
            $this->eloService->updateFromMatch($model);
        }

        if ($model instanceof StatisticRow) {
            $this->predictionService->queueForStatistics($model);
        }
    }

    private function hasCompletedResult(array $attributes): bool
    {
        return in_array($attributes['status'] ?? null, ['finished', 'played', 'completed'])
            && isset($attributes['score_home'], $attributes['score_away'])
            && ((int) $attributes['score_home'] + (int) $attributes['score_away']) > 0;
    }
}
