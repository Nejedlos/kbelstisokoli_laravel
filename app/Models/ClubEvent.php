<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

use Spatie\Translatable\HasTranslations;

class ClubEvent extends Model
{
    use HasTranslations;

    protected $table = 'club_events';

    protected $fillable = [
        'title',
        'event_type',
        'team_id',
        'description',
        'location',
        'starts_at',
        'ends_at',
        'is_public',
        'rsvp_enabled',
    ];

    public $translatable = ['title', 'description'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_public' => 'boolean',
        'rsvp_enabled' => 'boolean',
    ];

    /**
     * Tým, pro který je akce určena (pokud existuje).
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Docházka k této akci.
     */
    public function attendances(): MorphMany
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }
}
