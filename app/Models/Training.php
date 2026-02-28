<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Training extends Model
{
    protected $fillable = [
        'location',
        'starts_at',
        'ends_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Docházka k tomuto tréninku.
     */
    public function attendances(): MorphMany
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    /**
     * Záznamy docházky s rozporem.
     */
    public function mismatches(): MorphMany
    {
        return $this->attendances()->where('is_mismatch', true);
    }

    public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_training');
    }
}
