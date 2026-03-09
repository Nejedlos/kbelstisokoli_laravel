<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchPrediction extends Model
{
    protected $fillable = [
        'basketball_match_id',
        'season_id',
        'team_id',
        'computed_at',
        'model_version',
        'probability_win',
        'probability_loss',
        'confidence',
        'factors',
        'explanation_points',
        'expires_at',
    ];

    protected $casts = [
        'computed_at' => 'datetime',
        'probability_win' => 'float',
        'probability_loss' => 'float',
        'factors' => 'array',
        'explanation_points' => 'array',
        'expires_at' => 'datetime',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(BasketballMatch::class, 'basketball_match_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
