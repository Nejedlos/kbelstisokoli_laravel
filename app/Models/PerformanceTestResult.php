<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceTestResult extends Model
{
    protected $fillable = [
        'scenario',
        'url',
        'label',
        'section',
        'duration_ms',
        'query_count',
        'query_time_ms',
        'memory_mb',
        'opcache_enabled',
    ];

    protected $casts = [
        'opcache_enabled' => 'boolean',
        'duration_ms' => 'float',
        'query_time_ms' => 'float',
        'memory_mb' => 'float',
        'query_count' => 'integer',
    ];
}
