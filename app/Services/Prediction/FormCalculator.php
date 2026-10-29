<?php

namespace App\Services\Prediction;

use App\Models\BasketballMatch;
use App\Support\MatchResultHelper;
use Illuminate\Support\Carbon;

class FormCalculator
{
    /**
     * Calculate form delta for last N matches.
     */
    public function calculateFormDelta(BasketballMatch $match, int $limit = 5): array
    {
        if (! $match->scheduled_at) {
            return [
                'delta' => 0,
                'wins' => 0,
                'count' => 0,
                'avg_diff' => 0,
            ];
        }

        $lastMatches = BasketballMatch::forHistoricalPredictionTeam($match->team_id)
            ->where('season_id', $match->season_id)
            ->where('scheduled_at', '<', $match->scheduled_at)
            ->whereIn('status', ['finished', 'played', 'completed'])
            ->whereRaw('(score_home + score_away) > 0')
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
            if ($m->is_home) {
                if ($m->score_home > $m->score_away) {
                    $wins++;
                }
                $totalDiff += ($m->score_home - $m->score_away);
            } else {
                if ($m->score_away > $m->score_home) {
                    $wins++;
                }
                $totalDiff += ($m->score_away - $m->score_home);
            }
        }

        return $this->summarize($wins, $totalDiff, $lastMatches->count());
    }

    /**
     * Source previews contain results for both sides, including opponents whose
     * other games are not represented by our internal match records.
     */
    public function calculateSourceFormDelta(BasketballMatch $match, string $side, int $limit = 5): array
    {
        $empty = ['delta' => 0, 'wins' => 0, 'count' => 0, 'avg_diff' => 0];
        if (! $match->scheduled_at || ! in_array($side, ['team', 'opponent'], true)) {
            return $empty;
        }

        $sourceSide = $side === 'team'
            ? ($match->is_home ? 'home' : 'away')
            : ($match->is_home ? 'away' : 'home');
        $teamName = $side === 'team' ? $match->official_team_name : $match->official_opponent_name;
        $rows = $match->metadata['last_matches'][$sourceSide] ?? [];
        if (! is_array($rows)) {
            return $empty;
        }

        $datedRows = [];
        foreach ($rows as $index => $row) {
            if (! is_array($row) || ($date = $this->eligibleSourceDate($row['date'] ?? null, $match)) === null) {
                continue;
            }
            $datedRows[] = ['date' => $date, 'index' => $index, 'row' => $row];
        }
        usort($datedRows, fn ($left, $right) => strcmp($right['date'], $left['date']) ?: $left['index'] <=> $right['index']);

        $wins = 0;
        $totalDiff = 0;
        $count = 0;
        $seen = [];
        foreach ($datedRows as $datedRow) {
            $row = $datedRow['row'];
            $externalId = (string) ($row['external_id'] ?? '');
            if ($externalId !== '' && isset($seen[$externalId])) {
                continue;
            }

            $result = MatchResultHelper::for($row, $teamName);
            if ($result['ourScore'] === null || ($result['ourScore'] + $result['opponentScore']) === 0) {
                continue;
            }

            if ($externalId !== '') {
                $seen[$externalId] = true;
            }
            $wins += (int) $result['isWin'];
            $totalDiff += $result['ourScore'] - $result['opponentScore'];
            $count++;
            if ($count >= $limit) {
                break;
            }
        }

        return $count > 0 ? $this->summarize($wins, $totalDiff, $count) : $empty;
    }

    private function eligibleSourceDate(mixed $date, BasketballMatch $match): ?string
    {
        if (! is_string($date)) {
            return null;
        }

        if (preg_match('/\b(\d{4})-(\d{1,2})-(\d{1,2})\b/', $date, $parts)) {
            $year = (int) $parts[1];
            $month = (int) $parts[2];
            $day = (int) $parts[3];
        } elseif (preg_match('/\b(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})\b/', $date, $parts)) {
            $year = (int) $parts[3];
            $month = (int) $parts[2];
            $day = (int) $parts[1];
        } else {
            return null;
        }

        if (! checkdate($month, $day, $year)) {
            return null;
        }

        $canonical = sprintf('%04d-%02d-%02d', $year, $month, $day);

        return $canonical < $match->scheduled_at->toDateString()
            && $match->season?->containsDate(Carbon::createMidnightDate($year, $month, $day))
                ? $canonical
                : null;
    }

    private function summarize(int $wins, int $totalDiff, int $count): array
    {
        $avgDiff = $totalDiff / $count;
        $winRate = $wins / $count;

        // form_delta = clamp((wins_last5 - 2.5) * 20 + (avg_diff_last5 * 2), -60, +60)
        // wins_last5 in the formula seems to assume 5 matches. Let's adapt it to winRate.
        // (winRate * 5 - 2.5) * 20 + (avgDiff * 2)
        $delta = ($winRate * 5 - 2.5) * 20 + ($avgDiff * 2);
        $delta = max(-60, min(60, $delta));

        return [
            'delta' => $delta,
            'wins' => $wins,
            'count' => $count,
            'avg_diff' => $avgDiff,
        ];
    }
}
