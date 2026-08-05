<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class AuditLog extends Model
{
    use HasFactory, Prunable;

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

    /**
     * Definice záznamů k automatickému smazání (Prunable).
     * Mažeme záznamy starší než 7 dní.
     */
    public function prunable(): \Illuminate\Database\Eloquent\Builder
    {
        return static::where('occurred_at', '<', now()->subDays(7));
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
