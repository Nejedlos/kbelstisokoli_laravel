<?php

namespace App\Models;

use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasSeo, HasTranslations;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
        'is_visible',
        'head_code',
        'footer_code',
    ];

    public $translatable = ['title', 'content'];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('sort_order');
    }
}
