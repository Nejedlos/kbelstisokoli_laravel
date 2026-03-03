<?php

namespace App\Support;

use Illuminate\Support\Str;

class MatchIdentityKey
{
    /**
     * Generuje stabilní identifikační klíč pro zápas.
     *
     * @param int|string $seasonId
     * @param string $teamSlug
     * @param mixed $scheduledAt
     * @param bool $isHome
     * @param string $opponentName
     * @param string|null $round
     * @return string
     */
    public static function make(
        $seasonId,
        string $teamSlug,
        $scheduledAt,
        bool $isHome,
        string $opponentName,
        ?string $round = null
    ): string {
        $date = $scheduledAt instanceof \DateTimeInterface
            ? $scheduledAt->format('Y-m-d')
            : date('Y-m-d', strtotime($scheduledAt));

        $opponentNormalized = Str::slug($opponentName);
        $homeFlag = $isHome ? 'home' : 'away';

        $parts = [
            $seasonId,
            $teamSlug,
            $date,
            $homeFlag,
            $opponentNormalized,
        ];

        if ($round) {
            $parts[] = Str::slug($round);
        }

        return implode(':', $parts);
    }
}
