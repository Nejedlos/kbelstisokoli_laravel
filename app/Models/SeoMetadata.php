<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

class SeoMetadata extends Model
{
    use HasTranslations;

    protected $table = 'seo_metadatas';

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'title',
        'description',
        'keywords',
        'og_title',
        'og_description',
        'og_image',
        'canonical_url',
        'robots_index',
        'robots_follow',
        'twitter_card',
        'structured_data_override',
    ];

    public $translatable = [
        'title',
        'description',
        'keywords',
        'og_title',
        'og_description',
    ];

    protected $casts = [
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
        'structured_data_override' => 'array',
    ];

    /**
     * Get the parent seoable model (Page, Post, etc.).
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
