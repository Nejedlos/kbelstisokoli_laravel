<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalAnalyticsEvent extends Model
{
    protected $fillable = [
        'event_type',
        'area',
        'method',
        'path',
        'route_name',
        'route_action',
        'full_url_hash',
        'status_code',
        'response_time_ms',
        'user_id',
        'user_type',
        'guard',
        'is_authenticated',
        'visitor_hash',
        'session_hash',
        'ip_hash',
        'user_agent',
        'referer',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'is_authenticated' => 'boolean',
        'status_code' => 'integer',
        'response_time_ms' => 'integer',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
