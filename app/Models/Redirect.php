<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = [
        'source_path',
        'target_path',
        'target_url',
        'target_type',
        'status_code',
        'is_active',
        'match_type',
        'priority',
        'hits_count',
        'last_hit_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_hit_at' => 'datetime',
        'priority' => 'integer',
        'status_code' => 'integer',
        'hits_count' => 'integer',
    ];

    /**
     * Normalizace source path při ukládání.
     */
    protected static function booted(): void
    {
        static::saving(function (Redirect $redirect) {
            // Zajistíme, že cesta začíná lomítkem
            if ($redirect->source_path && !str_starts_with($redirect->source_path, '/')) {
                $redirect->source_path = '/' . $redirect->source_path;
            }

            // Normalizace target path
            if ($redirect->target_type === 'internal' && $redirect->target_path && !str_starts_with($redirect->target_path, '/')) {
                $redirect->target_path = '/' . $redirect->target_path;
            }
        });
    }

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
