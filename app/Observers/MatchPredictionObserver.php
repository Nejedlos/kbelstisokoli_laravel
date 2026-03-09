<?php

namespace App\Observers;

use App\Jobs\ComputeMatchPredictionJob;
use App\Models\BasketballMatch;
use App\Models\StatisticRow;

class MatchPredictionObserver
{
    public function saved($model): void
    {
        if ($model instanceof BasketballMatch) {
            // Jen pro budoucí nebo čerstvě odehrané zápasy
            if ($model->status === 'planned' || $model->wasChanged(['score_home', 'score_away', 'status'])) {
                ComputeMatchPredictionJob::dispatch($model->id);
            }
        }

        if ($model instanceof StatisticRow) {
            if ($model->basketball_match_id) {
                ComputeMatchPredictionJob::dispatch($model->basketball_match_id);
            }
        }
    }

    public function deleted($model): void
    {
        if ($model instanceof BasketballMatch) {
            // Predikce se smaže kaskádou v DB
        }

        if ($model instanceof StatisticRow) {
            if ($model->basketball_match_id) {
                ComputeMatchPredictionJob::dispatch($model->basketball_match_id);
            }
        }
    }
}
