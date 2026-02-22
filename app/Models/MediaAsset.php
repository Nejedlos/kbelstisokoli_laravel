<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
                    $newFileName = \Illuminate\Support\Str::slug($asset->title) . '.' . $media->extension;
                    if ($media->file_name !== $newFileName) {
                        $media->file_name = $newFileName;
                        $media->save();
                    }
                }
            }
        });
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
    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(1200)
            ->sharpen(10);
    }
}
