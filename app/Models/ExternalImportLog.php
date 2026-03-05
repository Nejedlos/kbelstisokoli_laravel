<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalImportLog extends Model
{
    protected $fillable = [
        'external_import_run_id',
        'model_type',
        'model_id',
        'action',
        'old_values',
        'new_values',
        'message',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function externalImportRun(): BelongsTo
    {
        return $this->belongsTo(ExternalImportRun::class);
    }

    public function model()
    {
        return $this->morphTo();
    }
}
