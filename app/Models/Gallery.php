<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Spatie\Translatable\HasTranslations;

class Gallery extends Model
{
    use HasTranslations;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'is_public',
        'is_visible',
        'variant',
        'cover_asset_id',
        'published_at',
    ];

    public $translatable = ['title', 'description'];

    protected $casts = [
        'is_public' => 'boolean',
        'is_visible' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function coverAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'cover_asset_id');
    }

    public function mediaAssets(): BelongsToMany
    {
        return $this->belongsToMany(MediaAsset::class, 'gallery_media')
            ->withPivot(['sort_order', 'caption_override', 'is_visible'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }
}
