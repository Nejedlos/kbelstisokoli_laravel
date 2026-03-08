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
        'team_name_in_source',
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

    /**
     * Zjistí počet selhání v řadě pro tuto konfiguraci.
     */
    public function getFailCountInARow(): int
    {
        return ExternalImportRun::where('team_id', $this->team_id)
            ->where('season_id', $this->season_id)
            ->where('source_key', $this->source_key)
            ->orderByDesc('started_at')
            ->get()
            ->takeWhile(fn ($run) => in_array($run->status, ['failed', 'partial_failed']))
            ->count();
    }
}
