<?php

namespace App\Models;

use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class PhotoPool extends Model
{
    use HasSeo, HasTranslations;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'event_type',
        'event_date',
        'is_public',
        'is_visible',
        'pending_import_queue',
        'is_processing_import',
    ];

    public $translatable = ['title', 'description'];

    protected $casts = [
        'event_date' => 'date',
        'is_public' => 'boolean',
        'is_visible' => 'boolean',
        'is_processing_import' => 'boolean',
        'pending_import_queue' => 'array',
    ];

    /**
     * Týmy, ke kterým tato galerie patří.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'photo_pool_team');
    }

    /**
     * Tým, ke kterému tato galerie patří (pro zpětnou kompatibilitu).
     */
    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

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
