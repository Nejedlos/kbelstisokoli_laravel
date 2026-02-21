<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'attendable_id',
        'attendable_type',
        'status',
        'note',
        'internal_note',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    /**
     * Získá vlastníka docházky.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorfní vazba na událost (Training, BasketballMatch, ClubEvent).
     */
    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }
}
