<?php

namespace App\Jobs\Stats\Legacy;

use App\Models\ExternalImportRun;
use App\Models\LegacyImportBatch;
use App\Models\LegacyImportFile;
use App\Models\Season;
use App\Models\StatisticRow;
use App\Models\StatisticSet;
use App\Models\Team;
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

        // Kontrola, zda nebyl celý batch zrušen
        if ($batch->status === 'cancelled') {
            $file->update(['status' => 'cancelled', 'error_summary' => 'Zrušeno uživatelem skrze dávku.']);
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

            if ($content === null) {
                // Zkusíme disk 'local' pokud v 'public' není, protože batch_creator dává 'legacystats/...'
                // A storage_path('app/legacystats') odpovídá 'local' disku v Laravelu obvykle.
                $content = Storage::disk('local')->get($file->stored_path);
            }

            // Pokud to není v Laravel storage disku, zkusíme přímou cestu (pro CLI/Local)
            if ($content === null) {
                // Skutečná cesta v tomto prostředí je storage/app/legacystats/...
                // Ale stored_path může být "legacystats/file.html"
                $absolutePath = base_path('storage/app/'.$file->stored_path);
                if (file_exists($absolutePath)) {
                    $content = file_get_contents($absolutePath);
                }
            }

            if ($content === null) {
                throw new \Exception("Soubor {$file->stored_path} nebyl nalezen v úložišti.");
            }

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
            $classification = (new \App\Services\Stats\Legacy\LegacyFileClassifier)->classify($file->original_filename, $content);
            $extractedTables = $extractor->extract($content, $file->file_type, $classification['encoding']);

            // 3. Season & Team
            $season = $this->ensureSeason($file->detected_season_label);
            $team = $file->detected_team_slug ? Team::where('slug', $file->detected_team_slug)->first() : null;

            if (! $season) {
                throw new \Exception("Sezónu '{$file->detected_season_label}' se nepodařilo vytvořit/najít.");
            }

            $warnings = [];

            // Check for existing official stats
            $hasOfficialStats = StatisticSet::where('season_id', $season->id)
                ->where('source_type', 'external')
                ->when($team, fn ($q) => $q->whereHas('rows', fn ($rq) => $rq->where('team_id', $team->id)))
                ->exists();

            if ($hasOfficialStats) {
                $warnings[] = "K sezóně {$season->name}".($team ? " (tým {$team->slug})" : '').' již existují oficiální statistiky (external sync). Legacy data byla uložena odděleně.';
            }

            // 4. Persist Tables
            $totalImportedCount = 0;
            // $warnings inicializováno dříve pro checkOfficialStats

            foreach ($extractedTables as $tableDto) {
                $statSet = $this->ensureStatSet($tableDto, $season, $team, $file);

                DB::transaction(function () use ($tableDto, $statSet, $season, $team, $file, &$totalImportedCount) {
                    // Smazat staré řádky pro stejný set, sezónu a soubor
                    StatisticRow::where('statistic_set_id', $statSet->id)
                        ->where('season_id', $season->id)
                        ->where('source_metadata', 'LIKE', '%"original_filename":"' . $file->original_filename . '"%')
                        ->where('source_metadata', 'LIKE', '%"table_type":"' . $tableDto->type . '"%')
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
                                'table_type' => $tableDto->type,
                                'original_filename' => $file->original_filename,
                                'content_hash' => hash('sha256', serialize($row->values)),
                                'imported_at' => now()->toDateTimeString(),
                            ],
                        ]);
                        $totalImportedCount++;
                    }
                });

                $warnings = array_merge($warnings, $tableDto->warnings);
            }

            // 6. Update status
            $file->update([
                'status' => 'success',
                'imported_rows_count' => $totalImportedCount,
                'warnings_count' => count($warnings),
            ]);

            $run->update([
                'status' => 'success',
                'extracted_count' => $totalImportedCount,
                'imported_count' => $totalImportedCount,
                'finished_at' => now(),
                'metadata' => array_merge($run->metadata, ['warnings' => $warnings]),
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
        if (! $label) {
            return null;
        }

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

    protected function ensureStatSet($tableDto, $season, $team, $file): StatisticSet
    {
        $setName = match ($tableDto->type) {
            'players_shooting' => "Legacy: Střelba hráčů {$season->name}".($team ? " ({$team->name})" : ''),
            'players_summary' => "Legacy: Souhrn hráčů {$season->name}".($team ? " ({$team->name})" : ''),
            'team_matches_shooting' => "Legacy: Zápasy střelba {$season->name}".($team ? " ({$team->name})" : ''),
            'team_matches_fouls' => "Legacy: Zápasy fauly/val {$season->name}".($team ? " ({$team->name})" : ''),
            'league_table' => "Legacy: Konečná tabulka {$season->name}",
            default => "Legacy Import: {$tableDto->type} {$season->name}",
        };

        $setSlug = Str::slug("legacy-{$tableDto->type}-{$season->name}".($team ? "-{$team->slug}" : ''));

        return StatisticSet::updateOrCreate(
            ['slug' => $setSlug],
            [
                'name' => ['cs' => $setName, 'en' => $setName],
                'type' => Str::contains($tableDto->type, 'players') ? 'player' : 'team',
                'column_config' => $tableDto->columns,
                'source_type' => 'legacy',
                'scope' => 'season',
            ]
        );
    }
}
