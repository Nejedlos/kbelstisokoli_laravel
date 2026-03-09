<?php

namespace App\Observers;

use App\Jobs\ComputeMatchPredictionJob;
use App\Models\BasketballMatch;
use App\Models\StatisticRow;
use App\Services\Prediction\EloService;

class MatchPredictionObserver
{
    public function __construct(
        protected EloService $eloService
    ) {}

    public function saved($model): void
    {
        if ($model instanceof BasketballMatch) {
            // Jen pro budoucí nebo čerstvě odehrané zápasy
            if ($model->status === 'planned') {
                ComputeMatchPredictionJob::dispatch($model->id);
            }

            // Pokud se změnil výsledek u odehraného zápasu, přepočítáme Elo
            if ($model->status === 'finished' && $model->wasChanged(['score_home', 'score_away', 'status'])) {
                $this->eloService->updateFromMatch($model);
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
