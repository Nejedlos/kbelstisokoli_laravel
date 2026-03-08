<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackReport extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'severity',
        'title',
        'description',
        'steps',
        'url',
        'route_name',
        'locale',
        'user_agent',
        'viewport',
        'screen',
        'timezone',
        'source_area',
        'app_version',
        'ip',
        'status',
        'admin_notes',
        'screenshot_path',
        'logs_path',
        'network_path',
        'clicks_path',
        'performance_path',
        'dom_path',
        'breadcrumbs_path',
        'correlation_id',
        'meta',
    ];

    protected $casts = [
        'viewport' => 'array',
        'screen' => 'array',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
