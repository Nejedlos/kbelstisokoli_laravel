<?php

namespace App\Services\Prediction;

use App\Models\BasketballMatch;

class FormCalculator
{
    /**
     * Calculate form delta for last N matches.
     */
    public function calculateFormDelta(BasketballMatch $match, int $limit = 5): array
    {
        $lastMatches = BasketballMatch::where('team_id', $match->team_id)
            ->where('season_id', $match->season_id)
            ->where('scheduled_at', '<', $match->scheduled_at)
            ->whereNotNull('score_home')
            ->whereNotNull('score_away')
            ->orderBy('scheduled_at', 'desc')
            ->limit($limit)
            ->get();

        if ($lastMatches->isEmpty()) {
            return [
                'delta' => 0,
                'wins' => 0,
                'count' => 0,
                'avg_diff' => 0,
            ];
        }

        $wins = 0;
        $totalDiff = 0;
        foreach ($lastMatches as $m) {
            $isWinner = false;
            if ($m->is_home) {
                if ($m->score_home > $m->score_away) {
                    $isWinner = true;
                    $wins++;
                }
                $totalDiff += ($m->score_home - $m->score_away);
            } else {
                if ($m->score_away > $m->score_home) {
                    $isWinner = true;
                    $wins++;
                }
                $totalDiff += ($m->score_away - $m->score_home);
            }
        }

        $avgDiff = $totalDiff / $lastMatches->count();
        $winRate = $wins / $lastMatches->count();

        // form_delta = clamp((wins_last5 - 2.5) * 20 + (avg_diff_last5 * 2), -60, +60)
        // wins_last5 in the formula seems to assume 5 matches. Let's adapt it to winRate.
        // (winRate * 5 - 2.5) * 20 + (avgDiff * 2)
        $delta = ($winRate * 5 - 2.5) * 20 + ($avgDiff * 2);
        $delta = max(-60, min(60, $delta));

        return [
            'delta' => $delta,
            'wins' => $wins,
            'count' => $lastMatches->count(),
            'avg_diff' => $avgDiff,
        ];
    }
}
