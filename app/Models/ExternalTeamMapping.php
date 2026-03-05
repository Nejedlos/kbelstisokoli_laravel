<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalTeamMapping extends Model
{
    protected $fillable = [
        'source_key',
        'team_id',
        'external_team_id',
        'base_team_url',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function source(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ExternalStatSource::class, 'source_key', 'slug');
    }
}
