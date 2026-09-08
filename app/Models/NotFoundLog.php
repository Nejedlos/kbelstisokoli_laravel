<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotFoundLog extends Model
{
    protected $fillable = [
        'url',
        'referer',
        'user_agent',
        'ip_address',
        'hits_count',
        'last_seen_at',
        'status',
        'redirect_id',
        'is_ignored',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'hits_count' => 'integer',
        'is_ignored' => 'boolean',
    ];

    public function redirect(): BelongsTo
    {
        return $this->belongsTo(Redirect::class);
    }
}
