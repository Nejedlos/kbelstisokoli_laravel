<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalPlayerMatch extends Model
{
    protected $fillable = [
        'user_id',
        'basketball_match_id',
        'source_key',
        'external_id',
        'external_match_id',
        'match_date',
        'competition_label',
        'opponent_name',
        'venue',
        'scheduled_at',
        'number',
        'is_starter',
        'is_captain',
        'points',
        'two_points_made',
        'two_points_attempts',
        'three_points_made',
        'three_points_attempts',
        'free_throws_made',
        'free_throws_attempts',
        'free_throws_pct',
        'fouls',
        'minutes',
        'valuation',
        'plus_minus',
        'rebounds_offensive',
        'rebounds_defensive',
        'rebounds_total',
        'assists',
        'steals',
        'turnovers',
        'blocks',
        'fouls_drawn',
        'metadata',
    ];

    protected $casts = [
        'match_date' => 'date',
        'scheduled_at' => 'datetime',
        'basketball_match_id' => 'integer',
        'is_starter' => 'boolean',
        'is_captain' => 'boolean',
        'points' => 'integer',
        'two_points_made' => 'integer',
        'two_points_attempts' => 'integer',
        'three_points_made' => 'integer',
        'three_points_attempts' => 'integer',
        'free_throws_made' => 'integer',
        'free_throws_attempts' => 'integer',
        'free_throws_pct' => 'float',
        'fouls' => 'integer',
        'minutes' => 'integer',
        'valuation' => 'integer',
        'plus_minus' => 'integer',
        'rebounds_offensive' => 'integer',
        'rebounds_defensive' => 'integer',
        'rebounds_total' => 'integer',
        'assists' => 'integer',
        'steals' => 'integer',
        'turnovers' => 'integer',
        'blocks' => 'integer',
        'fouls_drawn' => 'integer',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function basketballMatch(): BelongsTo
    {
        return $this->belongsTo(BasketballMatch::class, 'basketball_match_id');
    }
}
