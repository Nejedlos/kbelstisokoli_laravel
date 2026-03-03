<?php

namespace App\Services\Stats;

use App\Models\StatisticRow;
use App\Models\StatisticSet;
use App\Services\Stats\Sync\StatisticSetService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlayerStatsService
{
    /**
     * Získá sezónní souhrn hráče.
     */
    public function getSeasonSummary(int $userId, int $seasonId, ?int $teamId = null): ?array
    {
        $query = StatisticRow::where('player_id', $userId)
            ->where('season_id', $seasonId)
            ->whereHas('statisticSet', function ($q) {
                $q->where('slug', StatisticSetService::PLAYER_SEASON_SUMMARY_SET);
            });

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        $row = $query->first();

        if ($row) {
            return $row->values;
        }

        // Fallback: Pokud summary neexistuje, zkusíme ho dopočítat z jednotlivých zápasů
        return $this->calculateSummaryFromMatches($userId, $seasonId, $teamId);
    }

    /**
     * Získá časovou řadu statistik zápas po zápase.
     */
    public function getPerGameSeries(int $userId, int $seasonId, ?int $teamId = null): Collection
    {
        $query = StatisticRow::with('basketballMatch')
            ->where('player_id', $userId)
            ->where('season_id', $seasonId)
            ->whereHas('statisticSet', function ($q) {
                $q->where('slug', StatisticSetService::MATCH_BOXSCORE_SET);
            })
            ->join('matches', 'statistic_rows.basketball_match_id', '=', 'matches.id')
            ->orderBy('matches.scheduled_at', 'asc')
            ->select('statistic_rows.*');

        if ($teamId) {
            $query->where('statistic_rows.team_id', $teamId);
        }

        return $query->get()->map(function ($row) {
            return [
                'match_id' => $row->basketball_match_id,
                'date' => $row->basketballMatch?->scheduled_at,
                'opponent' => $row->basketballMatch?->opponent?->name ?? 'Neznámý soupeř',
                'values' => $row->values,
            ];
        });
    }

    /**
     * Výpočet souhrnu on-the-fly (fallback).
     */
    protected function calculateSummaryFromMatches(int $userId, int $seasonId, ?int $teamId = null): ?array
    {
        $query = StatisticRow::where('player_id', $userId)
            ->where('season_id', $seasonId)
            ->whereHas('statisticSet', function ($q) {
                $q->where('slug', StatisticSetService::MATCH_BOXSCORE_SET);
            });

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            return null;
        }

        $totalPts = 0;
        $totalMin = 0;
        $gp = $rows->count();

        // Jednoduchý výpočet pro fallback
        foreach ($rows as $row) {
            $totalPts += ($row->values['pts'] ?? 0);
            $totalMin += ($row->values['minutes'] ?? 0);
        }

        return [
            'gp' => $gp,
            'pts_total' => $totalPts,
            'ppg' => $gp > 0 ? round($totalPts / $gp, 1) : 0,
            'minutes_avg' => $gp > 0 ? round($totalMin / $gp, 1) : 0,
            'is_fallback' => true,
        ];
    }
}
