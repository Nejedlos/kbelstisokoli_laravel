<?php

namespace App\Jobs;

use App\Models\BasketballMatch;
use App\Services\Prediction\PredictionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeMatchPredictionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $matchId;

    public function __construct(int $matchId)
    {
        $this->matchId = $matchId;
    }

    public function handle(PredictionService $predictionService): void
    {
        $match = BasketballMatch::find($this->matchId);
        if (!$match) {
            return;
        }

        $predictionService->predict($match);
    }
}
