<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalEntityMapping extends Model
{
    protected $fillable = [
        'source_key',
        'season_id',
        'entity_type',
        'external_id',
        'internal_type',
        'internal_id',
        'identity_key',
        'confidence',
        'metadata',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'confidence' => 'float',
        'metadata' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function internal()
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'internal_id');
    }
}
