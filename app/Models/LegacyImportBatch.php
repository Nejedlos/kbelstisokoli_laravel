<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyImportBatch extends Model
{
    protected $fillable = [
        'created_by_user_id',
        'title',
        'status',
        'total_files',
        'processed_files',
        'success_files',
        'failed_files',
        'started_at',
        'finished_at',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Označí dávku jako zrušenou.
     */
    public function cancel(?string $message = 'Zrušeno uživatelem'): void
    {
        $this->update([
            'status' => 'cancelled',
            'finished_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], ['cancel_message' => $message]),
        ]);
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_user_id');
    }

    public function files(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LegacyImportFile::class);
    }
}
