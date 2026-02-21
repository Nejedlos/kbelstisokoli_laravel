<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageBlock extends Model
{
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
}
