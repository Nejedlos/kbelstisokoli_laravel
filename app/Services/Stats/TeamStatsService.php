<?php

namespace App\Services\Stats;

use App\Models\BasketballMatch;
use App\Models\StatisticRow;
use App\Services\Stats\Sync\StatisticSetService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TeamStatsService
{
    /**
     * Získá sezónní souhrn týmu.
     */
    public function getSeasonSummary(int $teamId, int $seasonId): ?array
    {
        $row = StatisticRow::where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->whereNull('player_id')
            ->whereNull('basketball_match_id')
            ->whereHas('set', function ($q) {
                $q->where('slug', StatisticSetService::TEAM_SEASON_SUMMARY_SET);
            })
            ->first();

        if ($row) {
            return $row->values;
        }

        return $this->calculateSummaryFromMatches($teamId, $seasonId);
    }

    /**
     * Získá seznam nejlepších střelců týmu v sezóně.
     */
    public function getTopScorers(int $teamId, int $seasonId, int $limit = 10): Collection
    {
        // Zkusíme vzít předpočítané summary pro hráče
        $summaries = StatisticRow::with('player')
            ->where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->whereNotNull('player_id')
            ->whereNull('basketball_match_id')
            ->whereHas('set', function ($q) {
                $q->where('slug', StatisticSetService::PLAYER_SEASON_SUMMARY_SET);
            })
            ->get();

        if ($summaries->isNotEmpty()) {
            return $summaries->map(function ($row) {
                return [
                    'player_id' => $row->player_id,
                    'name' => $row->player?->name,
                    'pts_total' => $row->values['pts_total'] ?? 0,
                    'ppg' => $row->values['ppg'] ?? 0,
                    'gp' => $row->values['gp'] ?? 0,
                ];
            })->sortByDesc('ppg')->take($limit)->values();
        }

        // Pokud nemáme summaries, agregujeme z boxscoru
        return StatisticRow::with('player')
            ->where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->whereNotNull('player_id')
            ->whereNotNull('basketball_match_id')
            ->whereHas('set', function ($q) {
                $q->where('slug', StatisticSetService::MATCH_BOXSCORE_SET);
            })
            ->select('player_id', DB::raw('SUM(JSON_EXTRACT(`values`, "$.pts")) as pts_total'), DB::raw('COUNT(*) as gp'))
            ->groupBy('player_id')
            ->get()
            ->map(function ($row) {
                $pts = (int) $row->pts_total;
                $gp = (int) $row->gp;

                return [
                    'player_id' => $row->player_id,
                    'name' => $row->player?->name ?? 'Neznámý hráč',
                    'pts_total' => $pts,
                    'ppg' => $gp > 0 ? round($pts / $gp, 1) : 0,
                    'gp' => $gp,
                ];
            })->sortByDesc('ppg')->take($limit)->values();
    }

    /**
     * Získá bilanci výher a proher týmu.
     */
    public function getWinLossBalance(int $teamId, int $seasonId): array
    {
        $matches = BasketballMatch::where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->where('status', 'completed')
            ->get();

        $wins = 0;
        $losses = 0;

        foreach ($matches as $match) {
            $isHome = $match->is_home;
            $scoreHome = $match->score_home;
            $scoreAway = $match->score_away;

            if ($scoreHome === null || $scoreAway === null) {
                continue;
            }

            if ($isHome) {
                if ($scoreHome > $scoreAway) {
                    $wins++;
                } elseif ($scoreHome < $scoreAway) {
                    $losses++;
                }
            } else {
                if ($scoreAway > $scoreHome) {
                    $wins++;
                } elseif ($scoreAway < $scoreHome) {
                    $losses++;
                }
            }
        }

        return [
            'wins' => $wins,
            'losses' => $losses,
            'total' => $wins + $losses,
        ];
    }

    /**
     * Získá časovou řadu bodů týmu v sezóně.
     */
    public function getPointsSeries(int $teamId, int $seasonId): Collection
    {
        return BasketballMatch::where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->where('status', 'completed')
            ->orderBy('scheduled_at', 'asc')
            ->get()
            ->map(function ($match) {
                $isHome = $match->is_home;

                return [
                    'date' => $match->scheduled_at,
                    'opponent' => $match->opponent?->name ?? 'Neznámý soupeř',
                    'pts_for' => $isHome ? $match->score_home : $match->score_away,
                    'pts_against' => $isHome ? $match->score_away : $match->score_home,
                    'result' => ($isHome ? $match->score_home > $match->score_away : $match->score_away > $match->score_home) ? 'W' : 'L',
                ];
            });
    }

    /**
     * Získá formu týmu (posledních 5 zápasů).
     */
    public function getRecentForm(int $teamId, int $seasonId, int $limit = 5): Collection
    {
        return $this->getPointsSeries($teamId, $seasonId)->take(-$limit);
    }

    /**
     * Výpočet souhrnu on-the-fly (fallback).
     */
    protected function calculateSummaryFromMatches(int $teamId, int $seasonId): array
    {
        $balance = $this->getWinLossBalance($teamId, $seasonId);

        $matches = BasketballMatch::where('team_id', $teamId)
            ->where('season_id', $seasonId)
            ->where('status', 'completed')
            ->get();

        $ptsFor = 0;
        $ptsAgainst = 0;

        foreach ($matches as $match) {
            if ($match->is_home) {
                $ptsFor += ($match->score_home ?? 0);
                $ptsAgainst += ($match->score_away ?? 0);
            } else {
                $ptsFor += ($match->score_away ?? 0);
                $ptsAgainst += ($match->score_home ?? 0);
            }
        }

        $gp = $balance['total'];

        return [
            'gp' => $gp,
            'wins' => $balance['wins'],
            'losses' => $balance['losses'],
            'pts_for' => $ptsFor,
            'pts_against' => $ptsAgainst,
            'pts_avg' => $gp > 0 ? round($ptsFor / $gp, 1) : 0,
            'is_fallback' => true,
        ];
    }
}
