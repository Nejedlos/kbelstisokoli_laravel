<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalStatSource extends Model
{
    protected $fillable = [
        'name',
        'source_url',
        'source_type',
        'extractor_config',
        'mapping_config',
        'is_active',
        'last_run_at',
        'last_status',
        'notes',
    ];

    protected $casts = [
        'extractor_config' => 'array',
        'mapping_config' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
    ];
}
