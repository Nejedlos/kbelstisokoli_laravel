<?php

namespace App\Support;

use Illuminate\Support\Str;

class MatchIdentityKey
{
    /**
     * Generuje stabilní identifikační klíč pro zápas.
     *
     * @param  int|string  $seasonId
     * @param  mixed  $scheduledAt
     */
    public static function make(
        $seasonId,
        string $teamSlug,
        $scheduledAt,
        bool $isHome,
        string $opponentName,
        ?string $round = null
    ): string {
        if ($scheduledAt instanceof \DateTimeInterface) {
            $date = $scheduledAt->format('Y-m-d');
        } elseif (! empty($scheduledAt)) {
            $ts = is_numeric($scheduledAt) ? (int) $scheduledAt : strtotime($scheduledAt);
            $date = $ts ? date('Y-m-d', $ts) : 'unknown-date';
        } else {
            $date = 'unknown-date';
        }

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
