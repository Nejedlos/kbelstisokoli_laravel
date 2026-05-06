<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalAnalyticsDailySummary extends Model
{
    protected $fillable = [
        'date',
        'area',
        'event_type',
        'total_count',
        'unique_visitors',
        'unique_users',
        'avg_response_time_ms',
        'max_response_time_ms',
        'status_2xx_count',
        'status_3xx_count',
        'status_4xx_count',
        'status_5xx_count',
        'metadata',
    ];

    protected $casts = [
        'date' => 'date',
        'total_count' => 'integer',
        'unique_visitors' => 'integer',
        'unique_users' => 'integer',
        'avg_response_time_ms' => 'integer',
        'max_response_time_ms' => 'integer',
        'status_2xx_count' => 'integer',
        'status_3xx_count' => 'integer',
        'status_4xx_count' => 'integer',
        'status_5xx_count' => 'integer',
        'metadata' => 'array',
    ];
}
