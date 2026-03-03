<?php

namespace App\Jobs\Stats\Legacy;

use App\Models\LegacyImportBatch;
use App\Models\LegacyImportFile;
use App\Models\Season;
use App\Models\Team;
use App\Models\StatisticSet;
use App\Models\StatisticRow;
use App\Models\ExternalImportRun;
use App\Services\Stats\Legacy\Extractors\LegacyStatExtractor;
use App\Services\Stats\Sync\StatisticSetService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessLegacyImportFileJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $fileId
    ) {}

    public function handle(LegacyStatExtractor $extractor, StatisticSetService $setService): void
    {
        $file = LegacyImportFile::findOrFail($this->fileId);
        $batch = $file->batch;

        if ($file->status === 'success' || $file->status === 'skipped') {
            return;
        }

        $file->update(['status' => 'running']);

        // 1. Audit Run
        $run = ExternalImportRun::create([
            'source_key' => 'legacy',
            'run_type' => 'legacy_file',
            'target_external_id' => $file->original_filename,
            'status' => 'running',
            'started_at' => now(),
            'metadata' => [
                'batch_id' => $batch->id,
                'file_id' => $file->id,
                'detected_season' => $file->detected_season_label,
                'detected_team' => $file->detected_team_slug,
                'file_type' => $file->file_type,
            ],
        ]);

        $file->update(['import_run_id' => $run->id]);

        try {
            $content = Storage::disk('public')->get($file->stored_path);

            // Idempotence check (pokud už existuje stejný hash v úspěšném runu)
            $existingFile = LegacyImportFile::where('content_hash', $file->content_hash)
                ->where('status', 'success')
                ->where('id', '!=', $file->id)
                ->first();

            if ($existingFile) {
                $file->update(['status' => 'skipped']);
                $run->update(['status' => 'skipped', 'finished_at' => now()]);
                $this->updateBatchProgress($batch);
                return;
            }

            // 2. Parse
            $tableDto = $extractor->extract($content, $file->file_type);

            // 3. Season & Team
            $season = $this->ensureSeason($file->detected_season_label);
            $team = $file->detected_team_slug ? Team::where('slug', $file->detected_team_slug)->first() : null;

            if (!$season) {
                throw new \Exception("Sezónu '{$file->detected_season_label}' se nepodařilo vytvořit/najít.");
            }

            // 4. Statistic Set
            $setName = match ($file->file_type) {
                'players_stats' => "Legacy: Statistiky hráčů {$season->name}" . ($team ? " ({$team->name})" : ""),
                'team_stats' => "Legacy: Týmové statistiky {$season->name}" . ($team ? " ({$team->name})" : ""),
                'league_table' => "Legacy: Konečná tabulka {$season->name}",
                default => "Legacy Import: {$file->original_filename}",
            };

            $setSlug = Str::slug("legacy-{$file->file_type}-{$season->name}" . ($team ? "-{$team->slug}" : ""));

            $statSet = StatisticSet::updateOrCreate(
                ['slug' => $setSlug],
                [
                    'name' => ['cs' => $setName, 'en' => $setName],
                    'type' => match ($file->file_type) {
                        'players_stats' => 'player',
                        'team_stats' => 'team',
                        'league_table' => 'team',
                        default => 'player',
                    },
                    'column_config' => $tableDto->columns,
                    'source_type' => 'legacy',
                    'scope' => 'season',
                ]
            );

            // 5. Persist Rows
            $importedCount = 0;
            DB::transaction(function () use ($tableDto, $statSet, $season, $team, $file, &$importedCount) {
                // Smazat staré řádky pro stejný set a sezónu v rámci této dávky (pokud re-run)
                // Ale raději budeme čistit jen pokud force? Tady zatím prostě mažeme staré z tohoto souboru.
                StatisticRow::where('statistic_set_id', $statSet->id)
                    ->where('season_id', $season->id)
                    ->whereJsonContains('source_metadata->original_filename', $file->original_filename)
                    ->delete();

                foreach ($tableDto->rows as $row) {
                    StatisticRow::create([
                        'statistic_set_id' => $statSet->id,
                        'season_id' => $season->id,
                        'team_id' => $team?->id,
                        'row_label' => $row->rowLabel,
                        'values' => $row->values,
                        'source_metadata' => [
                            'source_type' => 'legacy',
                            'original_filename' => $file->original_filename,
                            'content_hash' => $file->content_hash,
                            'imported_at' => now()->toDateTimeString(),
                        ],
                    ]);
                    $importedCount++;
                }
            });

            // 6. Update status
            $file->update([
                'status' => 'success',
                'imported_rows_count' => $importedCount,
                'warnings_count' => count($tableDto->warnings),
            ]);

            $run->update([
                'status' => 'success',
                'extracted_count' => count($tableDto->rows),
                'imported_count' => $importedCount,
                'finished_at' => now(),
                'metadata' => array_merge($run->metadata, ['warnings' => $tableDto->warnings]),
            ]);

        } catch (\Exception $e) {
            $file->update([
                'status' => 'failed',
                'error_summary' => $e->getMessage(),
            ]);

            $run->update([
                'status' => 'failed',
                'error_summary' => $e->getMessage(),
                'finished_at' => now(),
            ]);
        }

        $this->updateBatchProgress($batch);
    }

    protected function ensureSeason(?string $label): ?Season
    {
        if (!$label) return null;

        return Season::firstOrCreate(
            ['name' => $label],
            ['is_active' => false]
        );
    }

    protected function updateBatchProgress(LegacyImportBatch $batch): void
    {
        $stats = $batch->files()
            ->selectRaw('count(*) as total,
                        sum(case when status in ("success", "failed", "skipped") then 1 else 0 end) as processed,
                        sum(case when status = "success" then 1 else 0 end) as success,
                        sum(case when status = "failed" then 1 else 0 end) as failed')
            ->first();

        $batch->update([
            'processed_files' => $stats->processed,
            'success_files' => $stats->success,
            'failed_files' => $stats->failed,
            'status' => ($stats->processed == $stats->total)
                ? ($stats->failed > 0 ? 'partial_failed' : 'success')
                : 'running',
            'finished_at' => ($stats->processed == $stats->total) ? now() : null,
        ]);
    }
}
