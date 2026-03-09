<?php

namespace App\Services\Prediction;

use App\Models\BasketballMatch;

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
        $count = count($mutualMatches);
        $teamName = $match->team->name;

        foreach ($mutualMatches as $m) {
            // Skóre je v metadata jako stringy nebo inty
            $scoreHome = (int)$m['score_home'];
            $scoreAway = (int)$m['score_away'];

            // Zjistíme, jestli jsme byli domácí nebo hosté v tom zápase
            // V metadata mutual_matches bývá team_home a team_away
            $isHomeInMatch = str_contains(strtolower($m['team_home']), strtolower($teamName));

            $isWin = false;
            if ($isHomeInMatch) {
                if ($scoreHome > $scoreAway) {
                    $isWin = true;
                    $wins++;
                }
                $totalDiff += ($scoreHome - $scoreAway);
            } else {
                if ($scoreAway > $scoreHome) {
                    $isWin = true;
                    $wins++;
                }
                $totalDiff += ($scoreAway - $scoreHome);
            }
        }

        $avgDiff = $totalDiff / $count;
        $winRate = $wins / $count;

        // Výpočet delty:
        // Pokud jsme vyhráli vše, chceme bonus. Pokud jsme prohráli vše, chceme postih.
        // Win rate 0.5 = neutrální (0 delta).
        // Max postih/bonus nastavíme na 100 Elo bodů.
        // (winRate - 0.5) * 2 * 100
        $delta = ($winRate - 0.5) * 2 * 100;

        // Přidáme vliv průměrného rozdílu skóre (max 50 Elo bodů)
        $delta += max(-50, min(50, $avgDiff * 2));

        return [
            'delta' => max(-150, min(150, $delta)),
            'wins' => $wins,
            'count' => $count,
            'avg_diff' => $avgDiff,
        ];
    }
}
