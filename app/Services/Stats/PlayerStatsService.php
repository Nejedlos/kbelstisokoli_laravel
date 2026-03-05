<?php

namespace App\Services\Stats;

use App\Models\StatisticRow;
use App\Services\Stats\Sync\StatisticSetService;
use Illuminate\Support\Collection;

class PlayerStatsService
{
    /**
     * Získá sezónní souhrn hráče.
     */
    public function getSeasonSummary(int $userId, int $seasonId, ?int $teamId = null): ?array
    {
        $query = StatisticRow::where('player_id', $userId)
            ->where('season_id', $seasonId)
            ->whereHas('set', function ($q) {
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
        $query = StatisticRow::with(['basketballMatch', 'basketballMatch.opponent'])
            ->where('player_id', $userId)
            ->where('season_id', $seasonId)
            ->whereHas('set', function ($q) {
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
                'is_home' => $row->basketballMatch?->is_home,
                'score_home' => $row->basketballMatch?->score_home,
                'score_away' => $row->basketballMatch?->score_away,
                'values' => $row->values,
            ];
        });
    }

    /**
     * Získá ranking hráče v týmu pro danou sezónu.
     */
    public function getRankings(int $userId, int $seasonId, int $teamId): array
    {
        $allSummaries = StatisticRow::where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->whereNotNull('player_id')
            ->whereNull('basketball_match_id')
            ->whereHas('set', function ($q) {
                $q->where('slug', StatisticSetService::PLAYER_SEASON_SUMMARY_SET);
            })
            ->get();

        if ($allSummaries->isEmpty()) {
            return [];
        }

        $metrics = ['pts_total', 'ppg', 'gp', 'minutes_avg'];
        $rankings = [];

        foreach ($metrics as $metric) {
            $sorted = $allSummaries->sortByDesc(function ($row) use ($metric) {
                return $row->values[$metric] ?? 0;
            })->values();

            $index = $sorted->search(function ($row) use ($userId) {
                return $row->player_id === $userId;
            });

            if ($index !== false) {
                $rankings[$metric] = [
                    'rank' => $index + 1,
                    'total' => $allSummaries->count(),
                    'value' => $sorted[$index]->values[$metric] ?? 0,
                ];
            }
        }

        return $rankings;
    }

    /**
     * Získá insighty (poučné zajímavosti) o výkonu hráče.
     */
    public function getInsights(int $userId, int $seasonId, int $teamId): array
    {
        $series = $this->getPerGameSeries($userId, $seasonId, $teamId);

        if ($series->count() < 1) {
            return [];
        }

        $insights = [];

        // Nejlepší zápas (podle bodů)
        $bestMatch = $series->sortByDesc('values.pts')->first();
        if ($bestMatch && ($bestMatch['values']['pts'] ?? 0) > 0) {
            $insights[] = [
                'type' => 'best_match',
                'label' => 'Nejlepší zápas sezóny',
                'value' => "{$bestMatch['values']['pts']} bodů proti {$bestMatch['opponent']}",
                'date' => $bestMatch['date']?->format('d.m.Y'),
            ];
        }

        // Stabilita (průměr posledních 5)
        if ($series->count() >= 3) {
            $last5 = $series->take(-5);
            $avgLast5 = round($last5->avg('values.pts'), 1);
            $insights[] = [
                'type' => 'stability',
                'label' => 'Aktuální forma',
                'value' => "{$avgLast5} PPG v posledních {$last5->count()} zápasech",
            ];
        }

        // Trend (posledních 3 vs celkový průměr)
        if ($series->count() >= 4) {
            $summary = $this->getSeasonSummary($userId, $seasonId, $teamId);
            $overallAvg = $summary['ppg'] ?? 0;
            $last3Avg = round($series->take(-3)->avg('values.pts'), 1);

            if ($last3Avg > $overallAvg + 2) {
                $insights[] = [
                    'type' => 'trend_up',
                    'label' => 'Stoupající tendence',
                    'value' => "Hraješ lépe než je tvůj sezónní průměr ({$last3Avg} vs {$overallAvg} PPG)",
                ];
            }
        }

        return $insights;
    }

    /**
     * Získá průměry týmu pro srovnání.
     */
    public function getTeamAverages(int $seasonId, int $teamId): array
    {
        $teamSummary = (new TeamStatsService)->getSeasonSummary($teamId, $seasonId);

        return [
            'pts_avg' => $teamSummary['pts_avg'] ?? 0,
            'gp' => $teamSummary['gp'] ?? 0,
        ];
    }

    /**
     * Výpočet souhrnu on-the-fly (fallback).
     */
    protected function calculateSummaryFromMatches(int $userId, int $seasonId, ?int $teamId = null): ?array
    {
        $query = StatisticRow::where('player_id', $userId)
            ->where('season_id', $seasonId)
            ->whereHas('set', function ($q) {
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
