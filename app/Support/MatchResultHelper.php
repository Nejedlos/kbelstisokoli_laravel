<?php

namespace App\Support;

class MatchResultHelper
{
    /**
     * Zjistí výsledek zápasu z pohledu konkrétního týmu (podle jména).
     *
     * @param array $matchData Pole s klíči team_home, team_away, score_home, score_away
     * @param string $teamName Název týmu, pro který chceme výsledek zjistit
     * @return array [isWin, isLoss, isDraw, resultLetter, textColor, bgColor, ourScore, opponentScore]
     */
    public function getResultForTeam(array $matchData, string $teamName): array
    {
        $scoreHome = (int)($matchData['score_home'] ?? 0);
        $scoreAway = (int)($matchData['score_away'] ?? 0);
        $teamHome = $matchData['team_home'] ?? '';
        $teamAway = $matchData['team_away'] ?? '';

        $isHome = str_contains(mb_strtolower($teamHome), mb_strtolower($teamName));

        $isWin = false;
        $isLoss = false;
        $isDraw = false;

        if ($scoreHome === $scoreAway) {
            $isDraw = true;
        } elseif ($isHome) {
            $isWin = $scoreHome > $scoreAway;
            $isLoss = $scoreHome < $scoreAway;
        } else {
            $isWin = $scoreAway > $scoreHome;
            $isLoss = $scoreAway < $scoreHome;
        }

        return [
            'isWin' => $isWin,
            'isLoss' => $isLoss,
            'isDraw' => $isDraw,
            'resultLetter' => $isWin ? 'V' : ($isLoss ? 'P' : 'R'),
            'textColor' => $isWin ? 'text-emerald-600' : ($isLoss ? 'text-rose-600' : 'text-slate-600'),
            'bgColor' => $isWin ? 'bg-emerald-500' : ($isLoss ? 'bg-rose-500' : 'bg-slate-500'),
            'ourScore' => $isHome ? $scoreHome : $scoreAway,
            'opponentScore' => $isHome ? $scoreAway : $scoreHome,
        ];
    }

    /**
     * Zjistí výsledek zápasu na základě is_home a skóre.
     */
    public static function getResult(bool $isHome, ?int $scoreHome, ?int $scoreAway): array
    {
        if (is_null($scoreHome) || is_null($scoreAway)) {
            return [
                'isWin' => false,
                'isLoss' => false,
                'isDraw' => false,
                'resultLetter' => '',
                'textColor' => 'text-slate-400',
                'bgColor' => 'bg-slate-100',
                'ourScore' => null,
                'opponentScore' => null,
            ];
        }

        $isWin = $isHome ? $scoreHome > $scoreAway : $scoreAway > $scoreHome;
        $isDraw = $scoreHome === $scoreAway;
        $isLoss = !$isWin && !$isDraw;

        return [
            'isWin' => $isWin,
            'isLoss' => $isLoss,
            'isDraw' => $isDraw,
            'resultLetter' => $isWin ? 'V' : ($isLoss ? 'P' : 'R'),
            'textColor' => $isWin ? 'text-emerald-600' : ($isLoss ? 'text-rose-600' : 'text-slate-600'),
            'bgColor' => $isWin ? 'bg-emerald-500' : ($isLoss ? 'bg-rose-500' : 'bg-slate-500'),
            'ourScore' => $isHome ? $scoreHome : $scoreAway,
            'opponentScore' => $isHome ? $scoreAway : $scoreHome,
        ];
    }

    /**
     * Statická verze pro snadné použití v šablonách.
     */
    public static function for(array $matchData, string $teamName): array
    {
        return (new self())->getResultForTeam($matchData, $teamName);
    }
}
