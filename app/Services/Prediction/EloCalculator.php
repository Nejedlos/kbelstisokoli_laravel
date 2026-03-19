<?php

namespace App\Services\Prediction;

class EloCalculator
{
    public const INITIAL_ELO = 1500;
    public const HOME_ADVANTAGE = 50;
    public const K_FACTOR_DEFAULT = 20;
    public const K_FACTOR_LOW_DATA = 30;
    public const K_FACTOR_HIGH_DATA = 15;

    /**
     * Calculate expected win probability.
     */
    public function calculateExpected(float $eloTeam, float $eloOpponent, bool $isHome = false): float
    {
        $ratingDiff = ($eloOpponent - ($eloTeam + ($isHome ? self::HOME_ADVANTAGE : 0)));
        return 1 / (1 + 10 ** ($ratingDiff / 800));
    }

    /**
     * Calculate new Elo ratings after a match.
     */
    public function calculateNewRatings(float $eloTeam, float $eloOpponent, float $outcome, int $scoreDiff, bool $isHome = false, int $gamesPlayed = 0): array
    {
        $expected = $this->calculateExpected($eloTeam, $eloOpponent, $isHome);
        $k = $this->getKFactor($gamesPlayed);

        // Margin of victory multiplier
        $marginMultiplier = log(1 + abs($scoreDiff)) * (2.2 / (($eloTeam - $eloOpponent) * 0.001 + 2.2));

        $change = $k * $marginMultiplier * ($outcome - $expected);

        return [
            'new_elo_team' => $eloTeam + $change,
            'new_elo_opponent' => $eloOpponent - $change,
            'change' => $change
        ];
    }

    private function getKFactor(int $gamesPlayed): int
    {
        if ($gamesPlayed < 10) {
            return self::K_FACTOR_LOW_DATA;
        }
        if ($gamesPlayed > 30) {
            return self::K_FACTOR_HIGH_DATA;
        }
        return self::K_FACTOR_DEFAULT;
    }

    public function getInitialElo(): int
    {
        return self::INITIAL_ELO;
    }

    public function getHomeAdvantage(): int
    {
        return self::HOME_ADVANTAGE;
    }
}
