<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageBlock extends Model
{
    use Auditable;
    protected $fillable = [
        'page_id',
        'block_type',
        'sort_order',
        'is_visible',
        'data',
        'variant',
        'custom_id',
        'custom_class',
        'custom_attributes',
    ];

    protected $casts = [
        'data' => 'array',
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
        'custom_attributes' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    protected static function booted()
    {
        static::saved(function ($block) {
            try {
                \Illuminate\Support\Facades\Artisan::call('view:clear');
                \Illuminate\Support\Facades\Artisan::call('cache:clear');
            } catch (\Throwable $e) {
                // Ignorovat
            }
        });
    }
}
