<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MediaAsset extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'alt_text',
        'caption',
        'description',
        'type',
        'access_level',
        'is_public',
        'uploaded_by_id',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    protected static function booted()
    {
        // Automatické přejmenování fyzického souboru při změně názvu
        static::updating(function (MediaAsset $asset) {
            if ($asset->isDirty('title') && $asset->title) {
                foreach ($asset->getMedia('default') as $media) {
                    $newFileName = \Illuminate\Support\Str::slug($asset->title).'.'.$media->extension;
                    if ($media->file_name !== $newFileName) {
                        $media->file_name = $newFileName;
                        $media->save();
                    }
                }
            }
        });
    }

    public function photoPools(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(PhotoPool::class, 'photo_pool_media_asset')
            ->withPivot(['sort_order', 'caption_override', 'is_visible'])
            ->withTimestamps();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    /**
     * Zaregistruje kolekce médií.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->useDisk($this->access_level === 'public' ? 'media_public' : 'media_private')
            ->singleFile();
    }

    /**
     * Získá URL k souboru.
     */
    public function getUrl(string $conversion = ''): string
    {
        return $this->getFirstMediaUrl('default', $conversion);
    }

    /**
     * Zaregistruje konverze médií.
     */
    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(400)
            ->format(Manipulations::FORMAT_WEBP)
            ->nonQueued()
            ->sharpen(10);

        $this->addMediaConversion('optimized')
            ->width(1920)
            ->height(1920)
            ->format(Manipulations::FORMAT_WEBP)
            ->optimize()
            ->nonQueued();

        $this->addMediaConversion('original')
            ->width(2560)
            ->height(2560)
            ->keepOriginalImageFormat()
            ->optimize()
            ->nonQueued();
    }
}
