<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatisticRow extends Model
{
    protected $fillable = [
        'statistic_set_id',
        'player_id',
        'team_id',
        'basketball_match_id',
        'season_id',
        'row_label',
        'row_order',
        'is_visible',
        'values',
        'source_metadata',
    ];

    protected $casts = [
        'values' => 'array',
        'source_metadata' => 'array',
        'is_visible' => 'boolean',
        'row_order' => 'integer',
    ];

    public function set(): BelongsTo
    {
        return $this->belongsTo(StatisticSet::class, 'statistic_set_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(BasketballMatch::class, 'basketball_match_id');
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}
