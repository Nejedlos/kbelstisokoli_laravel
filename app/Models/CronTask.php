<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CronTask extends Model
{
    protected $fillable = [
        'name',
        'description',
        'command',
        'expression',
        'is_active',
        'last_run_at',
        'last_status',
        'last_error_message',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(CronLog::class);
    }
}
