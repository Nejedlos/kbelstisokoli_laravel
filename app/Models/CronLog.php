<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CronLog extends Model
{
    protected $fillable = [
        'cron_task_id',
        'started_at',
        'finished_at',
        'status',
        'output',
        'error_message',
        'duration_ms',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(CronTask::class, 'cron_task_id');
    }
}
