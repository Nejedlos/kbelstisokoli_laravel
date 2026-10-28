<?php

namespace App\Support;

class MatchResultHelper
{
    /**
     * Zjistí výsledek zápasu z pohledu konkrétního týmu (podle jména).
     *
     * @param  array  $matchData  Pole s klíči team_home, team_away, score_home, score_away
     * @param  string  $teamName  Název týmu, pro který chceme výsledek zjistit
     * @return array [isWin, isLoss, isDraw, resultLetter, textColor, bgColor, ourScore, opponentScore]
     */
    public function getResultForTeam(array $matchData, string $teamName): array
    {
        $isHome = self::homeSideFor($matchData, $teamName);
        $scoreHome = $matchData['score_home'] ?? null;
        $scoreAway = $matchData['score_away'] ?? null;

        if ($isHome === null || ! is_numeric($scoreHome) || ! is_numeric($scoreAway)) {
            return self::getResult(false, null, null);
        }

        return self::getResult($isHome, (int) $scoreHome, (int) $scoreAway);
    }

    /**
     * Unknown names must never silently count as the away team.
     */
    public static function homeSideFor(array $matchData, string $teamName): ?bool
    {
        $normalize = static fn (string $name): string => mb_strtolower(
            trim(preg_replace('/\\s+/u', ' ', str_replace("\u{00A0}", ' ', $name)))
        );
        $name = $normalize($teamName);
        if ($name === '') {
            return null;
        }

        $home = $normalize((string) ($matchData['team_home'] ?? '')) === $name;
        $away = $normalize((string) ($matchData['team_away'] ?? '')) === $name;

        return $home === $away ? null : $home;
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
        $isLoss = ! $isWin && ! $isDraw;

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
        return (new self)->getResultForTeam($matchData, $teamName);
    }

    /**
     * Formátuje skóre pro přehlednější zobrazení.
     * Odstraní tečky (tisícovky) a přidá mezery kolem dvojtečky.
     */
    public static function formatScore(string|int|null $score): string
    {
        if (is_null($score)) {
            return '';
        }

        // Převedeme na string, pokud to není
        $scoreStr = (string) $score;

        // Odstraníme tečky (tisícovky)
        $scoreStr = str_replace('.', '', $scoreStr);

        // Pokud obsahuje dvojtečku, přidáme kolem ní mezery (pokud tam už nejsou)
        if (str_contains($scoreStr, ':')) {
            $parts = explode(':', $scoreStr);
            $parts = array_map('trim', $parts);

            return implode(' : ', $parts);
        }

        return $scoreStr;
    }
}
