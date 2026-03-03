<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyImportFile extends Model
{
    protected $fillable = [
        'legacy_import_batch_id',
        'original_filename',
        'stored_path',
        'detected_season_label',
        'detected_team_slug',
        'file_type',
        'content_hash',
        'status',
        'error_summary',
        'warnings_count',
        'imported_rows_count',
        'import_run_id',
    ];

    public function batch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LegacyImportBatch::class, 'legacy_import_batch_id');
    }

    public function importRun(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ExternalImportRun::class, 'import_run_id');
    }
}
