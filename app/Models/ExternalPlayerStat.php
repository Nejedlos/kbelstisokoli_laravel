<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalPlayerStat extends Model
{
    protected $fillable = [
        'user_id',
        'source_key',
        'external_id',
        'season_label',
        'competition_label',
        'team_name',
        'games_played',
        'minutes_avg',
        'points_avg',
        'two_points_made_avg',
        'two_points_attempts_avg',
        'two_points_pct',
        'three_points_made_avg',
        'three_points_attempts_avg',
        'three_points_pct',
        'free_throws_made_avg',
        'free_throws_attempts_avg',
        'free_throws_pct',
        'rebounds_offensive_avg',
        'rebounds_defensive_avg',
        'rebounds_total_avg',
        'assists_avg',
        'steals_avg',
        'turnovers_avg',
        'blocks_avg',
        'fouls_avg',
        'fouls_received_avg',
        'valuation_avg',
        'plus_minus_avg',
        'is_career_total',
    ];

    protected $casts = [
        'games_played' => 'integer',
        'is_career_total' => 'boolean',
        'minutes_avg' => 'float',
        'points_avg' => 'float',
        'two_points_made_avg' => 'float',
        'two_points_attempts_avg' => 'float',
        'two_points_pct' => 'float',
        'three_points_made_avg' => 'float',
        'three_points_attempts_avg' => 'float',
        'three_points_pct' => 'float',
        'free_throws_made_avg' => 'float',
        'free_throws_attempts_avg' => 'float',
        'free_throws_pct' => 'float',
        'rebounds_offensive_avg' => 'float',
        'rebounds_defensive_avg' => 'float',
        'rebounds_total_avg' => 'float',
        'assists_avg' => 'float',
        'steals_avg' => 'float',
        'turnovers_avg' => 'float',
        'blocks_avg' => 'float',
        'fouls_avg' => 'float',
        'fouls_received_avg' => 'float',
        'valuation_avg' => 'float',
        'plus_minus_avg' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
