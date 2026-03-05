<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalImportRun extends Model
{
    protected $fillable = [
        'source_key',
        'season_id',
        'team_id',
        'run_type',
        'target_external_id',
        'status',
        'started_at',
        'finished_at',
        'extracted_count',
        'imported_count',
        'skipped_count',
        'content_hash',
        'error_summary',
        'metadata',
        'created_by_user_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function logs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExternalImportLog::class);
    }

    /**
     * Zahájí nový běh importu.
     */
    public static function start(string $sourceKey, int $seasonId, ?int $teamId, string $runType, ?string $targetExternalId): self
    {
        return self::create([
            'source_key' => $sourceKey,
            'season_id' => $seasonId,
            'team_id' => $teamId,
            'run_type' => $runType,
            'target_external_id' => $targetExternalId,
            'status' => 'running',
            'started_at' => now(),
            'metadata' => [],
        ]);
    }

    /**
     * Dokončí běh s úspěchem.
     */
    public function finish(array $counts = []): void
    {
        $this->update(array_merge($counts, [
            'status' => 'success',
            'finished_at' => now(),
        ]));
    }

    /**
     * Označí běh jako selhaný.
     */
    public function fail(\Throwable $e): void
    {
        $this->update([
            'status' => 'failed',
            'finished_at' => now(),
            'error_summary' => $e->getMessage()."\n".$e->getTraceAsString(),
        ]);
    }

    /**
     * Označí běh jako přeskočený (idempotence).
     */
    public function skip(): void
    {
        $this->update([
            'status' => 'skipped',
            'finished_at' => now(),
        ]);
    }

    /**
     * Ověří, zda je hash shodný s posledním úspěšným během.
     */
    public function isIdenticalToLast(?string $newHash): bool
    {
        if (! $newHash) {
            return false;
        }

        return $this->getLastHash() === $newHash;
    }

    /**
     * Získá poslední úspěšný nebo přeskočený běh pro dané parametry.
     */
    public function getLastHash(): ?string
    {
        return self::where('source_key', $this->source_key)
            ->where('run_type', $this->run_type)
            ->where('target_external_id', $this->target_external_id)
            ->whereIn('status', ['success', 'skipped'])
            ->where('id', '!=', $this->id)
            ->orderByDesc('started_at')
            ->value('content_hash');
    }

    /**
     * Označí běh jako částečně selhaný (např. selhal DOM, ale AI prošla).
     */
    public function partialFail(\Throwable $e): void
    {
        $this->update([
            'status' => 'partial_failed',
            'finished_at' => now(),
            'error_summary' => $e->getMessage(),
        ]);
    }

    /**
     * Zjistí počet selhání v řadě pro stejné parametry.
     */
    public function getFailCountInARow(): int
    {
        return self::where('source_key', $this->source_key)
            ->where('run_type', $this->run_type)
            ->where('target_external_id', $this->target_external_id)
            ->where('id', '<', $this->id)
            ->orderByDesc('started_at')
            ->get()
            ->takeWhile(fn ($run) => in_array($run->status, ['failed', 'partial_failed']))
            ->count();
    }

    /**
     * Aktualizuje metadata běhu.
     */
    public function updateMetadata(array $data): void
    {
        $this->update([
            'metadata' => array_merge($this->metadata ?? [], $data),
        ]);
    }

    /**
     * Přidá detailní log k běhu.
     */
    public function addLog(string $action, ?Model $model = null, ?array $oldValues = null, ?array $newValues = null, ?string $message = null): ExternalImportLog
    {
        return $this->logs()->create([
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'message' => $message,
        ]);
    }
}
