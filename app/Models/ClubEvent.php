<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

use App\Traits\Auditable;
use Spatie\Translatable\HasTranslations;

class ClubEvent extends Model
{
    use HasTranslations, Auditable;

    protected $table = 'club_events';

    protected $fillable = [
        'title',
        'event_type',
        'description',
        'location',
        'starts_at',
        'ends_at',
        'is_public',
        'rsvp_enabled',
    ];

    public $translatable = ['title', 'description'];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_public' => 'boolean',
        'rsvp_enabled' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Týmy, pro které je akce určena.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'club_event_team');
    }

    /**
     * Docházka k této akci.
     */
    public function attendances(): MorphMany
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }
}
