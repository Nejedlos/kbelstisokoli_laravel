<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BasketballMatch extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'team_id',
        'season_id',
        'opponent_id',
        'scheduled_at',
        'location',
        'is_home',
        'status',
        'score_home',
        'score_away',
        'notes_internal',
        'notes_public',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_home' => 'boolean',
        'score_home' => 'integer',
        'score_away' => 'integer',
    ];

    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function season(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function opponent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Opponent::class);
    }
}
