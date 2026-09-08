<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'status',
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'payload',
        'ip_address',
        'user_agent',
        'responsible_user_id',
        'internal_notes',
        'next_contact_at',
        'assigned_at',
        'status_changed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'next_contact_at' => 'datetime',
        'assigned_at' => 'datetime',
        'status_changed_at' => 'datetime',
    ];

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function changeStatus(string $status): void
    {
        $this->update([
            'status' => $status,
            'status_changed_at' => now(),
        ]);
    }

    public static function openStatuses(): array
    {
        return ['new', 'pending', 'in_progress', 'deferred'];
    }
}
