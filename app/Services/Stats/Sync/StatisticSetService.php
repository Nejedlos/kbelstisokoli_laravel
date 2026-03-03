<?php

namespace App\Services\Stats\Sync;

use App\Models\StatisticSet;
use Illuminate\Support\Str;

class StatisticSetService
{
    public const MATCH_BOXSCORE_SET = 'match-boxscore-external';
    public const PLAYER_SEASON_SUMMARY_SET = 'player-season-summary-external';
    public const TEAM_SEASON_SUMMARY_SET = 'team-season-summary-external';

    /**
     * Zajistí existenci základních statistických sad.
     */
    public function ensureBaseSets(): void
    {
        $this->ensureMatchBoxscoreSet();
        $this->ensurePlayerSeasonSummarySet();
        $this->ensureTeamSeasonSummarySet();
    }

    protected function ensureMatchBoxscoreSet(): StatisticSet
    {
        return StatisticSet::firstOrCreate(
            ['slug' => self::MATCH_BOXSCORE_SET],
            [
                'name' => 'Statistiky zápasu (Externí)',
                'type' => 'match',
                'source_type' => 'external',
                'scope' => ['match'],
                'column_config' => [
                    ['key' => 'pts', 'label' => 'Body', 'type' => 'number'],
                    ['key' => 'minutes', 'label' => 'Minuty', 'type' => 'number'],
                    ['key' => 'fg2_made', 'label' => '2B (P)', 'type' => 'number'],
                    ['key' => 'fg2_att', 'label' => '2B (V)', 'type' => 'number'],
                    ['key' => 'fg3_made', 'label' => '3B (P)', 'type' => 'number'],
                    ['key' => 'fg3_att', 'label' => '3B (V)', 'type' => 'number'],
                    ['key' => 'ft_made', 'label' => 'TH (P)', 'type' => 'number'],
                    ['key' => 'ft_att', 'label' => 'TH (V)', 'type' => 'number'],
                    ['key' => 'fouls', 'label' => 'Fauly', 'type' => 'number'],
                    ['key' => 'fouls_drawn', 'label' => 'Fauly+', 'type' => 'number'],
                    ['key' => 'assists', 'label' => 'Asistence', 'type' => 'number'],
                    ['key' => 'rebounds', 'label' => 'Doskoky', 'type' => 'number'],
                    ['key' => 'steals', 'label' => 'Zisky', 'type' => 'number'],
                    ['key' => 'turnovers', 'label' => 'Ztráty', 'type' => 'number'],
                    ['key' => 'blocks', 'label' => 'Bloky', 'type' => 'number'],
                    ['key' => 'plus_minus', 'label' => '+/-', 'type' => 'number'],
                    ['key' => 'efficiency', 'label' => 'VAL', 'type' => 'number'],
                ],
                'is_visible' => true,
                'status' => 'active',
            ]
        );
    }

    protected function ensurePlayerSeasonSummarySet(): StatisticSet
    {
        return StatisticSet::firstOrCreate(
            ['slug' => self::PLAYER_SEASON_SUMMARY_SET],
            [
                'name' => 'Sezónní souhrn hráče (Externí)',
                'type' => 'player',
                'source_type' => 'external',
                'scope' => ['season'],
                'column_config' => [
                    ['key' => 'gp', 'label' => 'Zápasy', 'type' => 'number'],
                    ['key' => 'pts_total', 'label' => 'Body celkem', 'type' => 'number'],
                    ['key' => 'ppg', 'label' => 'B/Z', 'type' => 'number'],
                    ['key' => 'minutes_avg', 'label' => 'Min/Z', 'type' => 'number'],
                    ['key' => 'fg2_pct', 'label' => '2B %', 'type' => 'number'],
                    ['key' => 'fg3_pct', 'label' => '3B %', 'type' => 'number'],
                    ['key' => 'ft_pct', 'label' => 'TH %', 'type' => 'number'],
                ],
                'is_visible' => true,
                'status' => 'active',
            ]
        );
    }

    protected function ensureTeamSeasonSummarySet(): StatisticSet
    {
        return StatisticSet::firstOrCreate(
            ['slug' => self::TEAM_SEASON_SUMMARY_SET],
            [
                'name' => 'Sezónní souhrn týmu (Externí)',
                'type' => 'team',
                'source_type' => 'external',
                'scope' => ['season'],
                'column_config' => [
                    ['key' => 'gp', 'label' => 'Zápasy', 'type' => 'number'],
                    ['key' => 'wins', 'label' => 'Výhry', 'type' => 'number'],
                    ['key' => 'losses', 'label' => 'Prohry', 'type' => 'number'],
                    ['key' => 'pts_for', 'label' => 'Body pro', 'type' => 'number'],
                    ['key' => 'pts_against', 'label' => 'Body proti', 'type' => 'number'],
                    ['key' => 'pts_avg', 'label' => 'Body Ø', 'type' => 'number'],
                ],
                'is_visible' => true,
                'status' => 'active',
            ]
        );
    }
}
