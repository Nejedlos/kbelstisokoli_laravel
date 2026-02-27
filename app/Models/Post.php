<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Post extends Model implements HasMedia
{
    use Auditable, HasFactory, HasSeo, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'is_visible',
        'publish_at',
        'featured_image',
        'head_code',
        'footer_code',
    ];

    public $translatable = ['title', 'excerpt', 'content'];

    protected $casts = [
        'publish_at' => 'datetime',
        'is_visible' => 'boolean',
    ];

    protected static function booted()
    {
        // Automatické přejmenování fyzického souboru při změně titulku článku pro SEO
        static::updating(function (Post $post) {
            if ($post->isDirty('title')) {
                foreach ($post->getMedia('featured_image') as $media) {
                    $newFileName = \Illuminate\Support\Str::slug($post->title).'.'.$media->extension;
                    if ($media->file_name !== $newFileName) {
                        $media->file_name = $newFileName;
                        $media->save();
                    }
                }
            }
        });
    }

    /**
     * Zaregistruje kolekce médií.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->useDisk('media_public') // Obrázky článků jsou veřejné
            ->singleFile();
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
            ->sharpen(10);

        $this->addMediaConversion('large')
            ->width(1200)
            ->height(800)
            ->format('webp')
            ->sharpen(10);
    }

    /**
     * Get the category that owns the post.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }
}
