<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMetadata extends Model
{
    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'title',
        'description',
        'keywords',
        'og_title',
        'og_description',
        'og_image',
    ];

    /**
     * Get the parent seoable model (Page, Post, etc.).
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
