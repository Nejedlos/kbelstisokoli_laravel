<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class PhotoPool extends Model
{
    use HasTranslations;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'event_type',
        'event_date',
        'is_public',
        'is_visible',
    ];

    public $translatable = ['title', 'description'];

    protected $casts = [
        'event_date' => 'date',
        'is_public' => 'boolean',
        'is_visible' => 'boolean',
    ];

    /**
     * Média přiřazená do tohoto poolu.
     */
    public function mediaAssets(): BelongsToMany
    {
        return $this->belongsToMany(MediaAsset::class, 'photo_pool_media_asset')
            ->withPivot(['sort_order', 'caption_override', 'is_visible'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }
}
