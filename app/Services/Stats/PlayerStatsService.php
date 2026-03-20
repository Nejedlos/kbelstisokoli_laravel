<?php

namespace App\Services\Stats;

use App\Models\StatisticRow;
use App\Services\Stats\Sync\StatisticSetService;
use Illuminate\Support\Collection;

class PlayerStatsService
{
    /**
     * Vypočítá index užitečnosti (VAL/efficiency) z dostupných boxscore metrik.
     * PTS + REB + AST + STL + BLK - (FGA - FGM) - (FTA - FTM) - TO - PF
     */
    protected function calculateEfficiencyFromValues(array $values): float
    {
        $pts = (float) ($values['pts'] ?? 0);
        $reb = (float) ($values['rebounds_total'] ?? 0);
        $ast = (float) ($values['assists'] ?? 0);
        $stl = (float) ($values['steals'] ?? 0);
        $blk = (float) ($values['blocks'] ?? 0);

        $fg2_made = (float) ($values['fg2_made'] ?? 0);
        $fg2_att = (float) ($values['fg2_att'] ?? 0);
        $fg3_made = (float) ($values['fg3_made'] ?? 0);
        $fg3_att = (float) ($values['fg3_att'] ?? 0);
        $ft_made = (float) ($values['ft_made'] ?? 0);
        $ft_att = (float) ($values['ft_att'] ?? 0);

        $to = (float) ($values['turnovers'] ?? 0);
        $fouls = (float) ($values['fouls'] ?? 0);

        // Neproměněné střely (pokud známe pokusy)
        $missed_fg = 0;
        if ($fg2_att > 0) {
            $missed_fg += ($fg2_att - $fg2_made);
        }
        if ($fg3_att > 0) {
            $missed_fg += ($fg3_att - $fg3_made);
        }

        $missed_ft = 0;
        if ($ft_att > 0) {
            $missed_ft += ($ft_att - $ft_made);
        }

        // Výpočet VAL
        // Pokud nemáme doskoky, asistence atd. (v nižších ligách), aspoň zohledníme Body - Fauly - Neproměněné hody.
        return $pts + $reb + $ast + $stl + $blk - $missed_fg - $missed_ft - $to - $fouls;
    }

    /**
     * Získá celkový kariérní přehled hráče.
     */
    public function getCareerOverview(int $userId): array
    {
        // 1. Získáme všechna externí data (historie)
        $externalStats = \App\Models\ExternalPlayerStat::where('user_id', $userId)
            ->where('is_career_total', false)
            ->orderBy('season_label', 'asc')
            ->get();

        // 2. Získáme všechna interní data (sezónní souhrny)
        $internalStats = \App\Models\StatisticRow::where('player_id', $userId)
            ->whereHas('set', function ($q) {
                $q->where('slug', StatisticSetService::PLAYER_SEASON_SUMMARY_SET);
            })
            ->with('season')
            ->get();

        // 3. Agregujeme všechna data do jednotné struktury podle sezóny
        $historyData = collect();

        // Přidáme externí data
        foreach ($externalStats as $row) {
            $seasonLabel = \App\Models\Season::normalizeName($row->season_label);

            if ($seasonLabel === 'Neznámá sezóna') {
                continue;
            }

            if (! $historyData->has($seasonLabel)) {
                $historyData->put($seasonLabel, [
                    'season' => $seasonLabel,
                    'gp' => 0,
                    'pts_total' => 0,
                    'efficiency_total' => 0,
                    'rebounds_total' => 0,
                    'assists_total' => 0,
                ]);
            }

            $current = $historyData->get($seasonLabel);
            $current['gp'] += $row->games_played;
            $current['pts_total'] += round($row->games_played * $row->points_avg);
            $current['efficiency_total'] += round($row->games_played * ($row->valuation_avg ?? 0));
            $current['rebounds_total'] += round($row->games_played * ($row->rebounds_total_avg ?? 0));
            $current['assists_total'] += round($row->games_played * ($row->assists_avg ?? 0));

            $historyData->put($seasonLabel, $current);
        }

        // Přidáme interní data (pokud už tam nejsou pro danou sezónu, nebo je sloučíme)
        foreach ($internalStats as $row) {
            $seasonLabel = \App\Models\Season::normalizeName($row->season?->name ?? 'Neznámá sezóna');

            if ($seasonLabel === 'Neznámá sezóna') {
                continue;
            }

            $values = $row->values;

            if (! $historyData->has($seasonLabel)) {
                $historyData->put($seasonLabel, [
                    'season' => $seasonLabel,
                    'gp' => 0,
                    'pts_total' => 0,
                    'efficiency_total' => 0,
                    'rebounds_total' => 0,
                    'assists_total' => 0,
                ]);
            }

            $current = $historyData->get($seasonLabel);
            $current['gp'] += ($values['gp'] ?? 0);
            $current['pts_total'] += ($values['pts_total'] ?? 0);
            $current['efficiency_total'] += ($values['efficiency_total'] ?? 0);
            $current['rebounds_total'] += ($values['rebounds_total'] ?? 0);
            $current['assists_total'] += ($values['assists_total'] ?? 0);

            $historyData->put($seasonLabel, $current);
        }

        // Vypočítáme průměry pro každou sezónu
        $finalHistory = $historyData->sortBy('season')->map(function ($data) {
            $gp = $data['gp'];

            return [
                'season' => $data['season'],
                'gp' => $gp,
                'pts_total' => $data['pts_total'],
                'ppg' => $gp > 0 ? round($data['pts_total'] / $gp, 1) : 0,
                'efficiency_avg' => $gp > 0 ? round($data['efficiency_total'] / $gp, 1) : 0,
                'rebounds_avg' => $gp > 0 ? round($data['rebounds_total'] / $gp, 1) : 0,
                'assists_avg' => $gp > 0 ? round($data['assists_total'] / $gp, 1) : 0,
            ];
        })->values();

        // 4. Celkové kariérní statistiky
        $totalGp = $finalHistory->sum('gp');
        $totalPts = $finalHistory->sum('pts_total');
        $avgPpg = $totalGp > 0 ? round($totalPts / $totalGp, 1) : 0;

        // Fallback na speciální kariérní řádek z externích dat (pokud obsahuje víc historie)
        $careerRow = \App\Models\ExternalPlayerStat::where('user_id', $userId)
            ->where('is_career_total', true)
            ->first();

        if ($careerRow) {
            if ($careerRow->games_played > $totalGp) {
                $totalGp = $careerRow->games_played;
                if ($totalPts == 0) {
                    $totalPts = round($careerRow->games_played * $careerRow->points_avg);
                }
                $avgPpg = $careerRow->points_avg;
            }
        }

        return [
            'summary' => [
                'total_gp' => (int) $totalGp,
                'total_pts' => (int) $totalPts,
                'ppg_avg' => (float) $avgPpg,
                'seasons_count' => $finalHistory->count(),
                'best_ppg_season' => $finalHistory->sortByDesc('ppg')->first(),
                'best_eff_season' => $finalHistory->sortByDesc('efficiency_avg')->first(),
            ],
            'history' => $finalHistory->toArray(),
        ];
    }

    /**
     * Získá sezónní souhrn hráče.
     */
    public function getSeasonSummary(int $userId, int $seasonId, ?int $teamId = null): ?array
    {
        // 1. Zkusíme najít interní souhrn (vytvořený systémem)
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

        // 2. Zkusíme najít externí souhrn (přímo z cz.basketball profilu) jako fallback
        $season = \App\Models\Season::find($seasonId);
        if ($season) {
            $normalizedSeason = \App\Models\Season::normalizeName($season->name); // např. 2024/2025
            $shortSeason = '';
            $parts = explode('/', $normalizedSeason);
            if (count($parts) === 2) {
                $shortSeason = $parts[0].'/'.substr($parts[1], 2, 2); // 2024/25
            }

            $externalStatQuery = \App\Models\ExternalPlayerStat::where('user_id', $userId)
                ->where(function ($q) use ($normalizedSeason, $shortSeason) {
                    $q->where('season_label', 'LIKE', "%{$normalizedSeason}%");
                    if ($shortSeason) {
                        $q->orWhere('season_label', 'LIKE', "%{$shortSeason}%");
                    }
                });

            // Pokud máme teamId, zkusíme najít staty pro daný tým (podle názvu)
            if ($teamId) {
                $team = \App\Models\Team::find($teamId);
                if ($team) {
                    $teamName = $team->getTranslation('name', 'cs');
                    $externalStatQuery->where('team_name', 'LIKE', "%{$teamName}%");
                }
            }

            $extStat = $externalStatQuery->first();
            if ($extStat) {
                // Převod ExternalPlayerStat na formát souhrnu
                return [
                    'gp' => $extStat->games_played,
                    'pts_total' => $extStat->points,
                    'ppg' => $extStat->points_avg,
                    'minutes_avg' => $extStat->minutes_avg,
                    'efficiency_avg' => $extStat->valuation_avg,
                    'rebounds_avg' => $extStat->rebounds_avg,
                    'assists_avg' => $extStat->assists_avg,
                    'steals_avg' => $extStat->steals_avg,
                    'blocks_avg' => $extStat->blocks_avg,
                    'fg2_avg' => $extStat->two_points_pct,
                    'fg3_avg' => $extStat->three_points_pct,
                    'ft_avg' => $extStat->free_throws_pct,
                    'fouls_avg' => $extStat->fouls_avg,
                    'is_fallback' => true,
                    'source' => 'external_stat',
                ];
            }
        }

        // 3. Fallback: Pokud summary neexistuje ani v externích datech, zkusíme ho dopočítat z jednotlivých zápasů (interních i externích)
        return $this->calculateSummaryFromMatches($userId, $seasonId, $teamId);
    }

    /**
     * Získá časovou řadu statistik zápas po zápase.
     */
    public function getPerGameSeries(int $userId, int $seasonId, ?int $teamId = null): Collection
    {
        $query = StatisticRow::with(['match', 'match.opponent', 'match.team'])
            ->where('statistic_rows.player_id', $userId)
            ->where('statistic_rows.season_id', $seasonId)
            ->whereHas('set', function ($q) {
                $q->where('slug', StatisticSetService::MATCH_BOXSCORE_SET);
            })
            ->join('matches', 'statistic_rows.basketball_match_id', '=', 'matches.id')
            ->orderBy('matches.scheduled_at', 'asc')
            ->select('statistic_rows.*');

        if ($teamId) {
            $query->where('statistic_rows.team_id', $teamId);
        }

        $internal = $query->get();

        if ($internal->isNotEmpty()) {
            // Seskupíme podle basketball_match_id, abychom eliminovali případné duplicity z joinu nebo chybných dat
            return $internal->unique('basketball_match_id')->map(function ($row) {
                $values = $row->values;
                // Zajistíme přítomnost klíče efficiency pro grafy.
                // Pokud chybí, pokusíme se ji vypočítat z dostupných metrik.
                if (! isset($values['efficiency']) || (float) $values['efficiency'] === 0.0) {
                    $values['efficiency'] = $values['valuation'] ?? $this->calculateEfficiencyFromValues($values);
                }

                return [
                    'match_id' => $row->basketball_match_id,
                    'date' => $row->match?->scheduled_at,
                    'opponent' => $row->match?->opponent?->name ?? 'Neznámý soupeř',
                    'is_home' => $row->match?->is_home,
                    'score_home' => $row->match?->score_home,
                    'score_away' => $row->match?->score_away,
                    'values' => $values,
                ];
            });
        }

        // Fallback na ExternalPlayerMatch
        $externalQuery = \App\Models\ExternalPlayerMatch::with(['basketballMatch', 'basketballMatch.opponent', 'basketballMatch.team'])
            ->where('user_id', $userId)
            ->where(function ($q) use ($seasonId) {
                // 1. Zápasy, které jsou spárované s interním zápasem dané sezóny
                $q->whereHas('basketballMatch', function ($mq) use ($seasonId) {
                    $mq->where('season_id', $seasonId);
                });

                // 2. Zápasy, které nejsou spárované, ale spadají tam podle data
                $season = \App\Models\Season::find($seasonId);
                if ($season) {
                    $normalized = \App\Models\Season::normalizeName($season->name);
                    $parts = explode('/', $normalized);
                    if (count($parts) === 2) {
                        $startYear = $parts[0];
                        $endYear = $parts[1];
                        $q->orWhere(function ($oq) use ($startYear, $endYear) {
                            $oq->whereNull('basketball_match_id')
                                ->whereBetween('match_date', ["{$startYear}-08-01", "{$endYear}-07-31"]);
                        });
                    }
                }
            })
            ->orderBy('scheduled_at', 'asc');

        if ($teamId) {
            $externalQuery->whereHas('basketballMatch', function ($mq) use ($teamId) {
                $mq->where('team_id', $teamId);
            });
        }

        $results = $externalQuery->get();

        // Eliminace duplicit: Pokud již máme pro stejný externí zápas interní data (StatisticRow),
        // externí záznam přeskočíme, abychom ho v grafu neměli 2x.
        $internalExternalIds = [];
        if ($internal->isNotEmpty()) {
            $internalExternalIds = $internal->map(function ($row) {
                return (string)($row->match?->metadata['external_id'] ?? '');
            })->filter()->toArray();
        }

        if (!empty($internalExternalIds)) {
            $results = $results->filter(function ($match) use ($internalExternalIds) {
                return !in_array((string)($match->external_match_id), $internalExternalIds);
            });
        }

        // Dodatečná filtrace v PHP pro externí zápasy (kvůli chybějícím sloupcům v SQL a staré DB)
        if ($teamId && $results->isNotEmpty()) {
            $team = \App\Models\Team::find($teamId);
            if ($team) {
                $teamName = $team->getTranslation('name', 'cs');
                $results = $results->filter(function ($match) use ($teamId, $teamName) {
                    // 1. Spárované s interním zápasem daného týmu (již odfiltrováno v SQL výše přes whereHas)
                    if ($match->basketball_match_id) {
                        return true;
                    }

                    // 2. Metadata nebo název týmu
                    $metaTeamId = $match->metadata['team_id'] ?? null;
                    if ($metaTeamId == $teamId) {
                        return true;
                    }

                    $matchTeamName = $match->team_name ?? ($match->metadata['team_name'] ?? '');
                    if ($matchTeamName && str_contains(strtolower($matchTeamName), strtolower($teamName))) {
                        return true;
                    }

                    // Pokud nemáme žádné informace o týmu u externího zápasu,
                    // v osobních statistikách ho raději zobrazíme (všechny zápasy hráče),
                    // aby graf nebyl prázdný, pokud hraje jen za náš klub obecně.
                    return empty($matchTeamName) && (empty($metaTeamId) || $metaTeamId == 0);
                });
            }
        }

        return $results->map(function ($match) {
            $isHome = $match->basketballMatch?->is_home;
            if ($isHome === null) {
                // Odhad podle názvu domácího týmu v metadatech
                $homeTeam = strtolower($match->metadata['home_team'] ?? '');
                $isHome = str_contains($homeTeam, 'kbely') || str_contains($homeTeam, 'sokol kbely');
            }

            return [
                'match_id' => $match->basketball_match_id,
                'date' => $match->scheduled_at,
                'opponent' => $match->opponent_name ?? $match->basketballMatch?->opponent?->name ?? 'Neznámý soupeř',
                'is_home' => $isHome,
                'score_home' => $match->basketballMatch?->score_home,
                'score_away' => $match->basketballMatch?->score_away,
                'values' => [
                    'pts' => $match->points,
                    'fg2_made' => $match->two_points_made,
                    'fg2_att' => $match->two_points_attempts,
                    'fg3_made' => $match->three_points_made,
                    'fg3_att' => $match->three_points_attempts,
                    'ft_made' => $match->free_throws_made,
                    'ft_att' => $match->free_throws_attempts,
                    'rebounds_total' => $match->rebounds_total,
                    'assists' => $match->assists,
                    'steals' => $match->steals,
                    'turnovers' => $match->turnovers,
                    'blocks' => $match->blocks,
                    'fouls' => $match->fouls,
                    'efficiency' => $match->valuation ?? ($match->metadata['valuation'] ?? 0),
                    'minutes' => $match->minutes,
                    'fouls_drawn' => $match->fouls_drawn,
                ],
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

        // Definice možných metrik s jejich prioritou (pokud mají data)
        $potentialMetrics = [
            'pts_total',
            'ppg',
            'gp',
            'efficiency_avg',
            'rebounds_avg',
            'minutes_avg',
            'fg3_total',
            'ft_pct',
            'fouls_avg',
        ];

        // Vždy chceme aspoň 6 karet, abychom naplnili grid 3x2
        // Nejprve zkusíme najít 6 metrik, které mají data
        $metricsWithData = [];
        foreach ($potentialMetrics as $metric) {
            $hasData = $allSummaries->max(function ($row) use ($metric) {
                return (float) ($row->values[$metric] ?? 0);
            }) > 0;

            if ($hasData) {
                $metricsWithData[] = $metric;
            }
        }

        // Pokud jich máme méně než 6, doplníme je o ty základní, i když jsou nulové
        $selectedMetrics = array_slice($metricsWithData, 0, 6);
        if (count($selectedMetrics) < 6) {
            foreach ($potentialMetrics as $metric) {
                if (! in_array($metric, $selectedMetrics)) {
                    $selectedMetrics[] = $metric;
                    if (count($selectedMetrics) >= 6) {
                        break;
                    }
                }
            }
        }

        $rankings = [];

        foreach ($selectedMetrics as $metric) {
            $values = $allSummaries->map(function ($row) use ($metric) {
                return (float) ($row->values[$metric] ?? 0);
            });

            $sorted = $allSummaries->sortByDesc(function ($row) use ($metric) {
                return (float) ($row->values[$metric] ?? 0);
            })->values();

            $index = $sorted->search(function ($row) use ($userId) {
                return $row->player_id === $userId;
            });

            if ($index !== false) {
                $rankings[$metric] = [
                    'rank' => $index + 1,
                    'total' => $allSummaries->count(),
                    'value' => $sorted[$index]->values[$metric] ?? 0,
                    'average' => round($values->avg(), 1),
                    'median' => round($values->median(), 1),
                    'best' => $values->max(),
                    'has_data' => $values->max() > 0,
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
                    'value' => "V posledních 3 zápasech hraješ lépe než je tvůj sezónní průměr ({$last3Avg} vs {$overallAvg} PPG)",
                ];
            } elseif ($last3Avg < $overallAvg - 2 && $overallAvg > 5) {
                $insights[] = [
                    'type' => 'trend_down',
                    'label' => 'Klesající tendence',
                    'value' => "V posledních 3 zápasech hraješ pod svůj sezónní průměr ({$last3Avg} vs {$overallAvg} PPG)",
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
        // Prioritně zkusíme najít zápasy v interním systému (StatisticRow)
        $internalQuery = StatisticRow::where('player_id', $userId)
            ->where('season_id', $seasonId)
            ->whereHas('set', function ($q) {
                $q->where('slug', StatisticSetService::MATCH_BOXSCORE_SET);
            });

        if ($teamId) {
            $internalQuery->where('team_id', $teamId);
        }

        $internalRows = $internalQuery->get()->unique('basketball_match_id');

        if ($internalRows->isNotEmpty()) {
            return $this->aggregateFromStatisticRows($internalRows);
        }

        // Pokud nejsou interní, zkusíme externí
        $externalQuery = \App\Models\ExternalPlayerMatch::where('user_id', $userId)
            ->where(function ($q) use ($seasonId) {
                $q->whereHas('basketballMatch', function ($mq) use ($seasonId) {
                    $mq->where('season_id', $seasonId);
                });

                $season = \App\Models\Season::find($seasonId);
                if ($season) {
                    $normalized = \App\Models\Season::normalizeName($season->name);
                    $parts = explode('/', $normalized);
                    if (count($parts) === 2) {
                        $startYear = $parts[0];
                        $endYear = $parts[1];
                        $q->orWhere(function ($oq) use ($startYear, $endYear) {
                            $oq->whereNull('basketball_match_id')
                                ->whereBetween('match_date', ["{$startYear}-08-01", "{$endYear}-07-31"]);
                        });
                    }
                }
            });

        if ($teamId) {
            $externalQuery->whereHas('basketballMatch', function ($mq) use ($teamId) {
                $mq->where('team_id', $teamId);
            });
        }

        $externalMatches = $externalQuery->get()->unique(function ($m) {
            return $m->basketball_match_id ?: ($m->external_match_id ?: $m->id);
        });

        // Filtrace v PHP (kvůli staré DB na produkci)
        if ($teamId && $externalMatches->isNotEmpty()) {
            $team = \App\Models\Team::find($teamId);
            if ($team) {
                $teamName = $team->getTranslation('name', 'cs');
                $externalMatches = $externalMatches->filter(function ($match) use ($teamId, $teamName) {
                    if ($match->basketball_match_id) {
                        return true;
                    }
                    $metaTeamId = $match->metadata['team_id'] ?? null;
                    if ($metaTeamId == $teamId) {
                        return true;
                    }
                    $matchTeamName = $match->team_name ?? ($match->metadata['team_name'] ?? '');
                    if ($matchTeamName && str_contains(strtolower($matchTeamName), strtolower($teamName))) {
                        return true;
                    }

                    return empty($matchTeamName) && empty($metaTeamId);
                });
            }
        }

        if ($externalMatches->isEmpty()) {
            return null;
        }

        return $this->aggregateFromExternalMatches($externalMatches);
    }

    protected function aggregateFromStatisticRows($rows): array
    {
        $totalPts = 0;
        $totalMin = 0;
        $totalFg2Made = 0;
        $totalFg2Att = 0;
        $totalFg3Made = 0;
        $totalFg3Att = 0;
        $totalFtMade = 0;
        $totalFtAtt = 0;
        $totalEff = 0;
        $totalReb = 0;
        $totalAst = 0;
        $totalStl = 0;
        $totalTov = 0;
        $totalBlk = 0;
        $totalFls = 0;
        $totalFlsD = 0;
        $gp = $rows->count();

        foreach ($rows as $row) {
            $rawPts = $row->values['pts'] ?? 0;
            $rawMin = $row->values['minutes'] ?? 0;
            $rawFg2 = $row->values['fg2_made'] ?? 0;
            $rawFg2Att = $row->values['fg2_att'] ?? 0;
            $rawFg3 = $row->values['fg3_made'] ?? 0;
            $rawFg3Att = $row->values['fg3_att'] ?? 0;
            $rawFt = $row->values['ft_made'] ?? 0;
            $rawFtAtt = $row->values['ft_att'] ?? 0;

            // Robustnější parsování poměrů (X/Y)
            if (is_string($rawFt) && str_contains($rawFt, '/')) {
                $parts = explode('/', $rawFt);
                $totalFtMade += (float) trim($parts[0]);
                $totalFtAtt += (float) trim($parts[1]);
            } else {
                $totalFtMade += (float) $rawFt;
                $totalFtAtt += (float) $rawFtAtt;
            }

            if (is_string($rawFg2) && str_contains($rawFg2, '/')) {
                $parts = explode('/', $rawFg2);
                $totalFg2Made += (float) trim($parts[0]);
                $totalFg2Att += (float) trim($parts[1]);
            } else {
                $totalFg2Made += (float) $rawFg2;
                $totalFg2Att += (float) $rawFg2Att;
            }

            if (is_string($rawFg3) && str_contains($rawFg3, '/')) {
                $parts = explode('/', $rawFg3);
                $totalFg3Made += (float) trim($parts[0]);
                $totalFg3Att += (float) trim($parts[1]);
            } else {
                $totalFg3Made += (float) $rawFg3;
                $totalFg3Att += (float) $rawFg3Att;
            }

            $totalPts += (float) $rawPts;
            $totalMin += (float) $rawMin;
            $eff = (float) ($row->values['efficiency'] ?? ($row->values['valuation'] ?? 0));
            if ($eff === 0.0) {
                $eff = $this->calculateEfficiencyFromValues($row->values);
            }
            $totalEff += $eff;
            $totalReb += (float) ($row->values['rebounds_total'] ?? 0);
            $totalAst += (float) ($row->values['assists'] ?? 0);
            $totalStl += (float) ($row->values['steals'] ?? 0);
            $totalTov += (float) ($row->values['turnovers'] ?? 0);
            $totalBlk += (float) ($row->values['blocks'] ?? 0);
            $totalFls += (float) ($row->values['fouls'] ?? 0);
            $totalFlsD += (float) ($row->values['fouls_drawn'] ?? 0);
        }

        return $this->formatSummary($gp, $totalPts, $totalMin, $totalFg2Made, $totalFg2Att, $totalFg3Made, $totalFg3Att, $totalFtMade, $totalFtAtt, $totalEff, $totalReb, $totalAst, $totalStl, $totalTov, $totalBlk, $totalFls, $totalFlsD, false);
    }

    protected function aggregateFromExternalMatches($matches): array
    {
        $totalPts = 0;
        $totalMin = 0;
        $totalFg2Made = 0;
        $totalFg2Att = 0;
        $totalFg3Made = 0;
        $totalFg3Att = 0;
        $totalFtMade = 0;
        $totalFtAtt = 0;
        $totalEff = 0;
        $totalReb = 0;
        $totalAst = 0;
        $totalStl = 0;
        $totalTov = 0;
        $totalBlk = 0;
        $totalFls = 0;
        $totalFlsD = 0;
        $gp = $matches->count();

        foreach ($matches as $match) {
            $totalPts += $match->points;
            $totalMin += $match->minutes;
            $totalFg2Made += $match->two_points_made;
            $totalFg2Att += $match->two_points_attempts;
            $totalFg3Made += $match->three_points_made;
            $totalFg3Att += $match->three_points_attempts;
            $totalFtMade += $match->free_throws_made;
            $totalFtAtt += $match->free_throws_attempts;
            $eff = (float) $match->valuation;
            if ($eff === 0.0) {
                $eff = $this->calculateEfficiencyFromValues([
                    'pts' => $match->points,
                    'rebounds_total' => $match->rebounds_total,
                    'assists' => $match->assists,
                    'steals' => $match->steals,
                    'blocks' => $match->blocks,
                    'turnovers' => $match->turnovers,
                    'fouls' => $match->fouls,
                    'fg2_made' => $match->two_points_made,
                    'fg2_att' => $match->two_points_attempts,
                    'fg3_made' => $match->three_points_made,
                    'fg3_att' => $match->three_points_attempts,
                    'ft_made' => $match->free_throws_made,
                    'ft_att' => $match->free_throws_attempts,
                ]);
            }
            $totalEff += $eff;
            $totalReb += $match->rebounds_total;
            $totalAst += $match->assists;
            $totalStl += $match->steals;
            $totalTov += $match->turnovers;
            $totalBlk += $match->blocks;
            $totalFls += $match->fouls;
            $totalFlsD += $match->fouls_drawn;
        }

        return $this->formatSummary($gp, $totalPts, $totalMin, $totalFg2Made, $totalFg2Att, $totalFg3Made, $totalFg3Att, $totalFtMade, $totalFtAtt, $totalEff, $totalReb, $totalAst, $totalStl, $totalTov, $totalBlk, $totalFls, $totalFlsD, true);
    }

    protected function formatSummary($gp, $totalPts, $totalMin, $totalFg2Made, $totalFg2Att, $totalFg3Made, $totalFg3Att, $totalFtMade, $totalFtAtt, $totalEff, $totalReb, $totalAst, $totalStl, $totalTov, $totalBlk, $totalFls, $totalFlsD, $isFallback): array
    {
        return [
            'gp' => $gp,

            // Celkové hodnoty (Totals)
            'pts_total' => $totalPts,
            'minutes_total' => $totalMin,
            'fg2_total' => $totalFg2Made,
            'fg2_att_total' => $totalFg2Att,
            'fg3_total' => $totalFg3Made,
            'fg3_att_total' => $totalFg3Att,
            'ft_total' => $totalFtMade,
            'ft_att_total' => $totalFtAtt,
            'efficiency_total' => $totalEff,
            'rebounds_total' => $totalReb,
            'assists_total' => $totalAst,
            'steals_total' => $totalStl,
            'turnovers_total' => $totalTov,
            'blocks_total' => $totalBlk,
            'fouls_total' => $totalFls,
            'fouls_drawn_total' => $totalFlsD,

            // Průměrné hodnoty (Averages)
            'ppg' => $gp > 0 ? round($totalPts / $gp, 1) : 0,
            'minutes_avg' => $gp > 0 ? round($totalMin / $gp, 1) : 0,

            // Střelba - procenta (jen pokud jsou k dispozici pokusy)
            'fg2_pct' => $totalFg2Att > 0 ? round(($totalFg2Made / $totalFg2Att) * 100, 1) : null,
            'fg3_pct' => $totalFg3Att > 0 ? round(($totalFg3Made / $totalFg3Att) * 100, 1) : null,
            'ft_pct' => $totalFtAtt > 0 ? round(($totalFtMade / $totalFtAtt) * 100, 1) : null,

            // Střelba - průměry (vždy užitečné, zejména u externích dat bez pokusů)
            'fg2_avg' => $gp > 0 ? round($totalFg2Made / $gp, 1) : 0,
            'fg3_avg' => $gp > 0 ? round($totalFg3Made / $gp, 1) : 0,
            'ft_avg' => $gp > 0 ? round($totalFtMade / $gp, 1) : 0,

            'efficiency_avg' => $gp > 0 ? round($totalEff / $gp, 1) : 0,
            'rebounds_avg' => $gp > 0 ? round($totalReb / $gp, 1) : 0,
            'assists_avg' => $gp > 0 ? round($totalAst / $gp, 1) : 0,
            'steals_avg' => $gp > 0 ? round($totalStl / $gp, 1) : 0,
            'turnovers_avg' => $gp > 0 ? round($totalTov / $gp, 1) : 0,
            'blocks_avg' => $gp > 0 ? round($totalBlk / $gp, 1) : 0,
            'fouls_avg' => $gp > 0 ? round($totalFls / $gp, 1) : 0,
            'fouls_drawn_avg' => $gp > 0 ? round($totalFlsD / $gp, 1) : 0,
            'is_fallback' => $isFallback,
        ];
    }
}
