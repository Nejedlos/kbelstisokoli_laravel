<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class ClubEvent extends Model implements HasMedia
{
    use Auditable, HasTranslations, InteractsWithMedia;

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
        'metadata',
    ];

    public $translatable = ['title', 'description'];

    protected $casts = [
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

    /**
     * Zaregistruje kolekce médií.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('poster')
            ->useDisk('media_public')
            ->singleFile()
            ->withResponsiveImages();

        $this->addMediaCollection('attachments')
            ->useDisk('media_public');
    }

    /**
     * Zaregistruje konverze médií.
     */
    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(300)
            ->format('webp')
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(800)
            ->format('webp')
            ->sharpen(10)
            ->nonQueued();
    }
}
