<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'occurred_at',
        'category',
        'event_key',
        'action',
        'severity',
        'actor_user_id',
        'actor_type',
        'subject_type',
        'subject_id',
        'subject_label',
        'route_name',
        'url',
        'ip_address',
        'ip_hash',
        'user_agent_summary',
        'request_id',
        'metadata',
        'changes',
        'is_system_event',
        'source',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'metadata' => 'array',
        'changes' => 'array',
        'is_system_event' => 'boolean',
    ];

    public function actor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
