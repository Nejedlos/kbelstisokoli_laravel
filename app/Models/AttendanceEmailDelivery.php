<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AttendanceEmailDelivery extends Model
{
    protected $fillable = ['user_id', 'attendable_id', 'attendable_type', 'kind', 'stage', 'status', 'sent_at', 'attempts', 'last_error'];

    protected $casts = ['sent_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }
}
