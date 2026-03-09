<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalPlayerMatch extends Model
{
    protected $fillable = [
        'user_id',
        'source_key',
        'external_id',
        'external_match_id',
        'match_date',
        'competition_label',
        'opponent_name',
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
    ];

    protected $casts = [
        'match_date' => 'date',
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
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
