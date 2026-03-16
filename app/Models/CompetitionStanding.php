<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionStanding extends Model
{
    protected $fillable = [
        'season_id',
        'competition_url',
        'competition_name',
        'team_name',
        'rank',
        'gp',
        'w',
        'l',
        'score',
        'points',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'rank' => 'integer',
        'gp' => 'integer',
        'w' => 'integer',
        'l' => 'integer',
        'points' => 'integer',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}
