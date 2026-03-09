<?php

namespace App\Services\Prediction;

use App\Models\BasketballMatch;
use App\Models\StatisticRow;

class RosterStrengthCalculator
{
    /**
     * Calculate roster strength for team and opponent.
     */
    public function calculateRosterStrength(BasketballMatch $match): array
    {
        if (!$match->scheduled_at) {
            return [
                'team' => ['total' => 0, 'count' => 0, 'players' => []],
                'opponent' => ['total' => 0, 'count' => 0, 'players' => []],
                'delta' => 0,
            ];
        }

        $teamStrength = $this->calculateTeamStrength($match->team_id, $match->season_id, $match->scheduled_at);
        $opponentStrength = $this->calculateOpponentStrength($match->opponent_id, $match->season_id, $match->scheduled_at);

        $delta = 0;
        if ($teamStrength['count'] > 0 && $opponentStrength['count'] > 0) {
            $delta = ($teamStrength['total'] - $opponentStrength['total']) * 2; // Arbitrary scaling
            $delta = max(-40, min(40, $delta));
        }

        return [
            'team' => $teamStrength,
            'opponent' => $opponentStrength,
            'delta' => $delta,
        ];
    }

    private function calculateTeamStrength(int $teamId, int $seasonId, $scheduledAt): array
    {
        // Get average points per player in the last 10 matches of the season
        $stats = StatisticRow::where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->whereHas('match', function ($query) use ($scheduledAt) {
                $query->where('scheduled_at', '<', $scheduledAt);
            })
            ->whereNotNull('player_id')
            ->get();

        if ($stats->isEmpty()) {
            return ['total' => 0, 'count' => 0, 'players' => []];
        }

        $playerStats = [];
        foreach ($stats as $row) {
            $playerId = $row->player_id;
            $points = (int) ($row->values['pts'] ?? 0);

            if (!isset($playerStats[$playerId])) {
                $playerStats[$playerId] = ['pts' => 0, 'games' => 0];
            }
            $playerStats[$playerId]['pts'] += $points;
            $playerStats[$playerId]['games']++;
        }

        $avgPoints = [];
        foreach ($playerStats as $playerId => $data) {
            $avgPoints[$playerId] = $data['pts'] / $data['games'];
        }

        arsort($avgPoints);
        $topPlayers = array_slice($avgPoints, 0, 8, true);
        $totalStrength = array_sum($topPlayers);

        return [
            'total' => $totalStrength,
            'count' => count($topPlayers),
            'players' => $topPlayers,
        ];
    }

    private function calculateOpponentStrength(int $opponentId, int $seasonId, $scheduledAt): array
    {
        // Opponent stats are also in StatisticRow, but player_id might be null or external
        $stats = StatisticRow::where('opponent_id', $opponentId)
            ->where('season_id', $seasonId)
            ->whereHas('match', function ($query) use ($scheduledAt) {
                $query->where('scheduled_at', '<', $scheduledAt);
            })
            ->get();

        if ($stats->isEmpty()) {
            return ['total' => 0, 'count' => 0, 'players' => []];
        }

        // If we have player-level stats for opponent
        $playerStats = [];
        foreach ($stats as $row) {
            $points = (int) ($row->values['pts'] ?? 0);
            $rowLabel = $row->row_label; // For opponent players, we might only have labels

            if (!$rowLabel) continue;

            if (!isset($playerStats[$rowLabel])) {
                $playerStats[$rowLabel] = ['pts' => 0, 'games' => 0];
            }
            $playerStats[$rowLabel]['pts'] += $points;
            $playerStats[$rowLabel]['games']++;
        }

        if (empty($playerStats)) {
             return ['total' => 0, 'count' => 0, 'players' => []];
        }

        $avgPoints = [];
        foreach ($playerStats as $label => $data) {
            $avgPoints[$label] = $data['pts'] / $data['games'];
        }

        arsort($avgPoints);
        $topPlayers = array_slice($avgPoints, 0, 8, true);
        $totalStrength = array_sum($topPlayers);

        return [
            'total' => $totalStrength,
            'count' => count($topPlayers),
            'players' => $topPlayers,
        ];
    }
}
