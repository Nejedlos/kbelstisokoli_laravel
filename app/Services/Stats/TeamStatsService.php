<?php

namespace App\Services\Stats;

use App\Models\BasketballMatch;
use App\Models\StatisticRow;
use App\Services\Stats\Sync\StatisticSetService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TeamStatsService
{
    /**
     * Získá sezónní souhrn týmu.
     */
    public function getSeasonSummary(int $teamId, int $seasonId): ?array
    {
        return Cache::remember("team_stats_{$teamId}_{$seasonId}_season_summary", 86400, function () use ($teamId, $seasonId) {
            // Preferujeme oficiální souhrn ze stránky soutěže, pokud je k dispozici
            if ($official = $this->getOfficialTeamSummary($teamId, $seasonId)) {
                return $official;
            }

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
        });
    }

    /**
     * Získá seznam všech hráčů týmu v sezóně s jejich statistikami.
     */
    public function getAllPlayersStats(int $teamId, int $seasonId): Collection
    {
        return Cache::remember("team_stats_{$teamId}_{$seasonId}_all_players", 86400, function () use ($teamId, $seasonId) {
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
                        'name' => $row->player?->name ?? 'Neznámý hráč',
                        'gp' => (int) ($row->values['gp'] ?? 0),
                        'pts_total' => (int) ($row->values['pts_total'] ?? 0),
                        'ppg' => (float) ($row->values['ppg'] ?? 0),
                        'fg3_total' => (int) ($row->values['fg3_total'] ?? 0),
                        'ft_att_total' => (int) ($row->values['ft_att_total'] ?? 0),
                        'fouls_total' => (int) ($row->values['fouls_total'] ?? 0),
                        'minutes_avg' => (float) ($row->values['minutes_avg'] ?? 0),
                        'fg2_pct' => (float) ($row->values['fg2_pct'] ?? 0),
                        'fg3_pct' => (float) ($row->values['fg3_pct'] ?? 0),
                        'ft_pct' => (float) ($row->values['ft_pct'] ?? 0),
                        'efficiency_avg' => (float) ($row->values['efficiency_avg'] ?? 0),
                        'rebounds_avg' => (float) ($row->values['rebounds_avg'] ?? 0),
                        'assists_avg' => (float) ($row->values['assists_avg'] ?? 0),
                        'steals_avg' => (float) ($row->values['steals_avg'] ?? 0),
                        'blocks_avg' => (float) ($row->values['blocks_avg'] ?? 0),
                        'fouls_avg' => (float) ($row->values['fouls_avg'] ?? 0),
                    ];
                });
            }

            // Pokud nemáme summaries, agregujeme z boxscoru pomocí SQL (MySQL 8 JSON optimalizace)
            $matchIds = $this->getTeamMatchesQuery($teamId, $seasonId)->pluck('id');

            if ($matchIds->isEmpty()) {
                return collect();
            }

            $stats = DB::table('statistic_rows')
                ->select('player_id')
                ->selectRaw("COUNT(*) as gp")
                ->selectRaw("SUM(CAST(JSON_EXTRACT(`values`, '$.pts') AS UNSIGNED)) as pts_total")
                ->selectRaw("SUM(CAST(JSON_EXTRACT(`values`, '$.fg3_made') AS UNSIGNED)) as fg3_total")
                ->selectRaw("SUM(CAST(JSON_EXTRACT(`values`, '$.ft_att') AS UNSIGNED)) as ft_att_total")
                ->selectRaw("SUM(CAST(JSON_EXTRACT(`values`, '$.fouls') AS UNSIGNED)) as fouls_total")
                ->selectRaw("AVG(CAST(JSON_EXTRACT(`values`, '$.minutes') AS DECIMAL(10,1))) as minutes_avg")
                ->selectRaw("AVG(CAST(JSON_EXTRACT(`values`, '$.efficiency') AS DECIMAL(10,1))) as efficiency_avg")
                ->selectRaw("AVG(CAST(JSON_EXTRACT(`values`, '$.rebounds_total') AS DECIMAL(10,1))) as rebounds_avg")
                ->selectRaw("AVG(CAST(JSON_EXTRACT(`values`, '$.assists') AS DECIMAL(10,1))) as assists_avg")
                ->selectRaw("AVG(CAST(JSON_EXTRACT(`values`, '$.steals') AS DECIMAL(10,1))) as steals_avg")
                ->selectRaw("AVG(CAST(JSON_EXTRACT(`values`, '$.blocks') AS DECIMAL(10,1))) as blocks_avg")
                ->whereIn('basketball_match_id', $matchIds)
                ->where('team_id', $teamId)
                ->where('season_id', $seasonId)
                ->whereNotNull('player_id')
                ->whereNotNull('basketball_match_id')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('statistic_sets')
                        ->whereColumn('statistic_sets.id', 'statistic_rows.statistic_set_id')
                        ->where('slug', StatisticSetService::MATCH_BOXSCORE_SET);
                })
                ->groupBy('player_id')
                ->get();

            if ($stats->isEmpty()) {
                return collect();
            }

            // Načtení jmen hráčů hromadně
            $playerIds = $stats->pluck('player_id')->toArray();
            $players = \App\Models\User::whereIn('id', $playerIds)->get()->keyBy('id');

            return $stats->map(function ($row) use ($players) {
                $player = $players->get($row->player_id);
                $gp = (int) $row->gp;
                $ptsTotal = (int) $row->pts_total;

                return [
                    'player_id' => $row->player_id,
                    'name' => $player?->name ?? 'Neznámý hráč',
                    'gp' => $gp,
                    'pts_total' => $ptsTotal,
                    'fg3_total' => (int) $row->fg3_total,
                    'ft_att_total' => (int) $row->ft_att_total,
                    'fouls_total' => (int) $row->fouls_total,
                    'ppg' => $gp > 0 ? round($ptsTotal / $gp, 1) : 0,
                    'minutes_avg' => round((float) $row->minutes_avg, 1),
                    'fg2_pct' => 0,
                    'fg3_pct' => 0,
                    'ft_pct' => 0,
                    'efficiency_avg' => round((float) $row->efficiency_avg, 1),
                    'rebounds_avg' => round((float) $row->rebounds_avg, 1),
                    'assists_avg' => round((float) $row->assists_avg, 1),
                    'steals_avg' => round((float) $row->steals_avg, 1),
                    'blocks_avg' => round((float) $row->blocks_avg, 1),
                    'fouls_avg' => round((float) $row->fouls_total / $gp, 1),
                ];
            });
        });
    }

    /**
     * Získá detailnější statistiky o zápasech týmu (nejvyšší výhry, prohry atd.).
     */
    public function getMatchStats(int $teamId, int $seasonId): array
    {
        return Cache::remember("team_stats_{$teamId}_{$seasonId}_match_stats", 86400, function () use ($teamId, $seasonId) {
            $matches = $this->getTeamMatchesQuery($teamId, $seasonId)
                ->with('opponent')
                ->whereNotNull('score_home')
                ->whereNotNull('score_away')
                ->get();

            if ($matches->isEmpty()) {
                return [
                    'biggest_win' => null,
                    'biggest_loss' => null,
                    'home_balance' => ['wins' => 0, 'losses' => 0, 'total' => 0],
                    'away_balance' => ['wins' => 0, 'losses' => 0, 'total' => 0],
                    'avg_margin' => 0,
                ];
            }

            $biggestWin = null;
            $biggestLoss = null;
            $homeWins = 0;
            $homeLosses = 0;
            $awayWins = 0;
            $awayLosses = 0;
            $totalMargin = 0;

            foreach ($matches as $match) {
                $isHome = $match->is_home;
                $scoreFor = $isHome ? $match->score_home : $match->score_away;
                $scoreAgainst = $isHome ? $match->score_away : $match->score_home;
                $margin = $scoreFor - $scoreAgainst;
                $totalMargin += $margin;

                $isWin = $margin > 0;
                $isLoss = $margin < 0;

                if ($isHome) {
                    if ($isWin) $homeWins++;
                    if ($isLoss) $homeLosses++;
                } else {
                    if ($isWin) $awayWins++;
                    if ($isLoss) $awayLosses++;
                }

                if ($isWin && (!$biggestWin || $margin > $biggestWin['margin'])) {
                    $biggestWin = [
                        'margin' => $margin,
                        'score' => "{$scoreFor}:{$scoreAgainst}",
                        'opponent' => $match->opponent?->name ?? 'Neznámý soupeř',
                        'date' => $match->scheduled_at,
                        'is_home' => $isHome
                    ];
                }

                if ($isLoss && (!$biggestLoss || $margin < $biggestLoss['margin'])) {
                    $biggestLoss = [
                        'margin' => abs($margin),
                        'score' => "{$scoreFor}:{$scoreAgainst}",
                        'opponent' => $match->opponent?->name ?? 'Neznámý soupeř',
                        'date' => $match->scheduled_at,
                        'is_home' => $isHome
                    ];
                }
            }

            return [
                'biggest_win' => $biggestWin,
                'biggest_loss' => $biggestLoss,
                'home_balance' => [
                    'wins' => $homeWins,
                    'losses' => $homeLosses,
                    'total' => $homeWins + $homeLosses,
                    'pct' => ($homeWins + $homeLosses) > 0 ? round(($homeWins / ($homeWins + $homeLosses)) * 100, 1) : 0
                ],
                'away_balance' => [
                    'wins' => $awayWins,
                    'losses' => $awayLosses,
                    'total' => $awayWins + $awayLosses,
                    'pct' => ($awayWins + $awayLosses) > 0 ? round(($awayWins / ($awayWins + $awayLosses)) * 100, 1) : 0
                ],
                'avg_margin' => round($totalMargin / $matches->count(), 1),
            ];
        });
    }

    /**
     * Získá lídry týmu v klíčových kategoriích.
     */
    public function getTeamLeaders(int $teamId, int $seasonId): array
    {
        return Cache::remember("team_stats_{$teamId}_{$seasonId}_leaders", 86400, function () use ($teamId, $seasonId) {
            $allStats = $this->getAllPlayersStats($teamId, $seasonId);

            if ($allStats->isEmpty()) {
                return [];
            }

            return [
                'scorers' => $allStats->sortByDesc('ppg')->first(),
                'mvp' => $allStats->sortByDesc('efficiency_avg')->first(),
                'rebounders' => $allStats->sortByDesc('rebounds_avg')->first(),
                'passers' => $allStats->sortByDesc('assists_avg')->first(),
                'ironman' => $allStats->sortByDesc('gp')->first(),
                'total_points' => $allStats->sortByDesc('pts_total')->first(),
                'snipers' => $allStats->sortByDesc('fg3_total')->first(),
                'th_kings' => $allStats->where('ft_att_total', '>=', 5)->sortByDesc('ft_pct')->first(),
                'defenders' => $allStats->sortByDesc('steals_avg')->first(),
            ];
        });
    }

    /**
     * Získá distribuci bodů mezi hráče pro donut graf.
     */
    public function getPointsDistribution(int $teamId, int $seasonId): array
    {
        return Cache::remember("team_stats_{$teamId}_{$seasonId}_points_dist", 86400, function () use ($teamId, $seasonId) {
            $allStats = $this->getAllPlayersStats($teamId, $seasonId);

            if ($allStats->isEmpty()) {
                return [];
            }

            $totalPoints = $allStats->sum('pts_total');

            if ($totalPoints === 0) {
                return [];
            }

            // Vezmeme top 5 a ostatní sloučíme
            $sorted = $allStats->sortByDesc('pts_total');
            $top5 = $sorted->take(5);
            $others = $sorted->slice(5);

            $labels = $top5->pluck('name')->toArray();
            $values = $top5->pluck('pts_total')->toArray();

            if ($others->isNotEmpty()) {
                $labels[] = 'Ostatní';
                $values[] = $others->sum('pts_total');
            }

            return [
                'labels' => $labels,
                'values' => $values,
            ];
        });
    }

    /**
     * Získá seznam nejlepších střelců týmu v sezóně.
     */
    public function getTopScorers(int $teamId, int $seasonId, int $limit = 10): Collection
    {
        return Cache::remember("team_stats_{$teamId}_{$seasonId}_top_scorers_{$limit}", 86400, function () use ($teamId, $seasonId, $limit) {
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

            // Pokud nemáme summaries, agregujeme z boxscoru v PHP (DB JSON_EXTRACT není všude dostupný)
            $matchIds = $this->getTeamMatchesQuery($teamId, $seasonId)->pluck('id');

            return StatisticRow::with('player')
                ->whereIn('basketball_match_id', $matchIds)
                ->where('team_id', $teamId)
                ->where('season_id', $seasonId)
                ->whereNotNull('player_id')
                ->whereNotNull('basketball_match_id')
                ->whereHas('set', function ($q) {
                    $q->where('slug', StatisticSetService::MATCH_BOXSCORE_SET);
                })
                ->get()
                ->groupBy('player_id')
                ->map(function ($playerRows, $playerId) {
                    $ptsTotal = $playerRows->sum(function ($row) {
                        return (int) ($row->values['pts'] ?? 0);
                    });
                    $gp = $playerRows->count();
                    $player = $playerRows->first()?->player;
                    return [
                        'player_id' => $playerId,
                        'name' => $player?->name ?? 'Neznámý hráč',
                        'pts_total' => $ptsTotal,
                        'ppg' => $gp > 0 ? round($ptsTotal / $gp, 1) : 0,
                        'gp' => $gp,
                    ];
                })->sortByDesc('ppg')->take($limit)->values();
        });
    }

    /**
     * Zkusí vrátit souhrn z oficiální tabulky soutěže, pokud je uložen v konfiguraci.
     */
    protected function getOfficialTeamSummary(int $teamId, int $seasonId): ?array
    {
        try {
            /** @var \App\Models\ExternalTeamSeasonConfig|null $config */
            $config = \App\Models\ExternalTeamSeasonConfig::where('team_id', $teamId)
                ->where('season_id', $seasonId)
                ->first();

            $official = $config?->metadata['official_standing'] ?? null;
            if ($official && isset($official['gp'], $official['w'], $official['l'], $official['score'])) {
                $gp = (int) $official['gp'];
                $wins = (int) $official['w'];
                $losses = (int) $official['l'];

                $ptsFor = 0;
                $ptsAgainst = 0;
                $scoreRaw = (string) ($official['score'] ?? '');
                // Odstraníme oddělovače tisíců (tečky, čárky, mezery včetně NBSP a úzké NBSP), ponecháme dvojtečku
                $scoreClean = str_replace([',', '.', ' ', "\xC2\xA0", "\xE2\x80\xAF"], '', $scoreRaw);
                if (preg_match('/(\d+)\s*[:\-]\s*(\d+)/', $scoreClean, $m)) {
                    $ptsFor = (int) $m[1];
                    $ptsAgainst = (int) $m[2];
                }

                $ptsAvg = $gp > 0 ? round($ptsFor / $gp, 1) : 0;
                $ptsAgainstAvg = $gp > 0 ? round($ptsAgainst / $gp, 1) : 0;

                return [
                    'gp' => $gp,
                    'wins' => $wins,
                    'losses' => $losses,
                    'pts_for' => $ptsFor,
                    'pts_against' => $ptsAgainst,
                    'pts_avg' => $ptsAvg,
                    'pts_against_avg' => $ptsAgainstAvg,
                    'source' => 'official',
                ];
            }
        } catch (\Throwable $e) {
            // Ignorujeme a vrátíme null
        }

        return null;
    }

    /**
     * Získá bilanci výher a proher týmu.
     */
    public function getWinLossBalance(int $teamId, int $seasonId): array
    {
        $matches = $this->getTeamMatchesQuery($teamId, $seasonId)
            ->whereNotNull('score_home')
            ->whereNotNull('score_away')
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
        return Cache::remember("team_stats_{$teamId}_{$seasonId}_points_series", 86400, function () use ($teamId, $seasonId) {
            return $this->getTeamMatchesQuery($teamId, $seasonId)
                ->with('opponent')
                ->whereNotNull('score_home')
                ->whereNotNull('score_away')
                ->orderBy('scheduled_at', 'asc')
                ->get()
                ->map(function ($match) {
                    $isHome = $match->is_home;

                    return [
                        'date' => $match->scheduled_at,
                        'opponent' => $match->opponent?->name ?? 'Neznámý soupeř',
                        'pts_for' => $isHome ? $match->score_home : $match->score_away,
                        'pts_against' => $isHome ? $match->score_away : $match->score_home,
                        'is_home' => $isHome,
                        'result' => ($isHome ? $match->score_home > $match->score_away : $match->score_away > $match->score_home) ? 'W' : 'L',
                    ];
                });
        });
    }

    /**
     * Získá formu týmu (posledních 5 zápasů).
     */
    public function getRecentForm(int $teamId, int $seasonId, int $limit = 5): array
    {
        $series = $this->getPointsSeries($teamId, $seasonId);
        $recent = $series->take(-$limit);

        if ($recent->isEmpty()) {
            return [
                'matches' => [],
                'avg_pts_for' => 0,
                'avg_pts_against' => 0,
                'count' => 0,
            ];
        }

        return [
            'matches' => $recent->values()->toArray(),
            'avg_pts_for' => round($recent->avg('pts_for'), 1),
            'avg_pts_against' => round($recent->avg('pts_against'), 1),
            'count' => $recent->count(),
        ];
    }

    /**
     * Výpočet souhrnu čistě z odehraných zápasů v databázi.
     */
    public function calculateSummaryFromMatches(int $teamId, int $seasonId): array
    {
        return Cache::remember("team_stats_{$teamId}_{$seasonId}_calc_summary", 86400, function () use ($teamId, $seasonId) {
            $balance = $this->getWinLossBalance($teamId, $seasonId);

            $matches = $this->getTeamMatchesQuery($teamId, $seasonId)
                ->whereNotNull('score_home')
                ->whereNotNull('score_away')
                ->get();

            $ptsFor = 0;
            $ptsAgainst = 0;

            foreach ($matches as $match) {
                if ($match->is_home) {
                    $ptsFor += (int) ($match->score_home ?? 0);
                    $ptsAgainst += (int) ($match->score_away ?? 0);
                } else {
                    $ptsFor += (int) ($match->score_away ?? 0);
                    $ptsAgainst += (int) ($match->score_home ?? 0);
                }
            }

            $gp = $balance['total'];
            $ptsAvg = $gp > 0 ? round($ptsFor / $gp, 1) : 0;
            $ptsAgainstAvg = $gp > 0 ? round($ptsAgainst / $gp, 1) : 0;

            return [
                'gp' => $gp,
                'wins' => $balance['wins'],
                'losses' => $balance['losses'],
                'pts_for' => $ptsFor,
                'pts_against' => $ptsAgainst,
                'pts_avg' => $ptsAvg,
                'pts_against_avg' => $ptsAgainstAvg,
                'source' => 'internal',
            ];
        });
    }

    /**
     * Pomocná metoda pro získání query builderu zápasů týmu v sezóně.
     * Kombinuje legacy team_id a relaci teams.
     */
    protected function getTeamMatchesQuery(int $teamId, int $seasonId): \Illuminate\Database\Eloquent\Builder
    {
        return BasketballMatch::where('season_id', $seasonId)
            ->where(function ($query) use ($teamId) {
                $query->where('team_id', $teamId)
                    ->orWhereHas('teams', function ($q) use ($teamId) {
                        $q->where('teams.id', $teamId);
                    });
            });
    }
}
