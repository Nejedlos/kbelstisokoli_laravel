<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalTeamSeasonConfig extends Model
{
    protected $fillable = [
        'source_key',
        'season_id',
        'team_id',
        'external_team_id',
        'external_season_year',
        'team_season_url',
        'matches_list_url',
        'competition_label',
        'is_enabled',
        'last_synced_at',
        'metadata',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'last_synced_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function season(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Season::class);
    }
}
