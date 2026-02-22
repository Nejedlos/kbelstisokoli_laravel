<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Post extends Model
{
    use HasSeo, HasTranslations, Auditable;

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

    /**
     * Get the category that owns the post.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'category_id');
    }
}
