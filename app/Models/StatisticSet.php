<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatisticSet extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'is_visible',
        'sort_order',
        'source_type',
        'scope',
        'column_config',
        'settings',
        'status',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'scope' => 'array',
        'column_config' => 'array',
        'settings' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Řádky v této sadě statistik.
     */
    public function rows(): HasMany
    {
        return $this->hasMany(StatisticRow::class)->orderBy('row_order');
    }
}
