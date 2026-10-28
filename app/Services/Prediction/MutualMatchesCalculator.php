<?php

namespace App\Services\Prediction;

use App\Models\BasketballMatch;
use App\Support\MatchResultHelper;

class MutualMatchesCalculator
{
    /**
     * Vypočítá vliv vzájemných zápasů (H2H) na Elo rating.
     * Vrací 'delta' (v Elo bodech) a 'wins' / 'count' pro vysvětlení.
     */
    public function calculateMutualMatchesDelta(BasketballMatch $match): array
    {
        $mutualMatches = $match->metadata['mutual_matches'] ?? [];
        if (empty($mutualMatches)) {
            return [
                'delta' => 0,
                'wins' => 0,
                'count' => 0,
                'avg_diff' => 0,
            ];
        }

        $wins = 0;
        $totalDiff = 0;
        $count = 0;
        $teamName = $match->official_team_name;

        foreach ($mutualMatches as $m) {
            $result = MatchResultHelper::for($m, $teamName);
            if ($result['ourScore'] === null || ($result['ourScore'] + $result['opponentScore']) === 0) {
                continue;
            }
            $count++;
            $wins += (int) $result['isWin'];
            $totalDiff += $result['ourScore'] - $result['opponentScore'];
        }

        if ($count === 0) {
            return ['delta' => 0, 'wins' => 0, 'count' => 0, 'avg_diff' => 0];
        }

        $avgDiff = $totalDiff / $count;
        $winRate = $wins / $count;

        // Výpočet delty:
        // Pokud jsme vyhráli vše, chceme bonus. Pokud jsme prohráli vše, chceme postih.
        // Win rate 0.5 = neutrální (0 delta).
        // Max postih/bonus nastavíme na 50 Elo bodů.
        // (winRate - 0.5) * 2 * 50
        $delta = ($winRate - 0.5) * 2 * 50;

        // Přidáme vliv průměrného rozdílu skóre (max 30 Elo bodů)
        $delta += max(-30, min(30, $avgDiff * 1.5));

        return [
            'delta' => max(-80, min(80, $delta)),
            'wins' => $wins,
            'count' => $count,
            'avg_diff' => $avgDiff,
        ];
    }
}
