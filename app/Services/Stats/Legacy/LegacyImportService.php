<?php

namespace App\Services\Stats\Legacy;

use App\Models\LegacyImportBatch;
use App\Models\LegacyImportFile;
use Illuminate\Support\Facades\Storage;

class LegacyImportService
{
    public function __construct(
        protected LegacyFileClassifier $classifier
    ) {}

    public function processUploads(LegacyImportBatch $batch, array $filePaths): void
    {
        $batchTotal = count($filePaths);
        $batch->update(['total_files' => $batchTotal]);

        foreach ($filePaths as $tempPath) {
            $filename = basename($tempPath);
            $content = Storage::disk('public')->get($tempPath);

            $classification = $this->classifier->classify($filename, $content);

            $finalPath = "legacy_import/{$batch->id}/{$filename}";
            Storage::disk('public')->move($tempPath, $finalPath);

            LegacyImportFile::create([
                'legacy_import_batch_id' => $batch->id,
                'original_filename' => $filename,
                'stored_path' => $finalPath,
                'detected_season_label' => $classification['season'],
                'detected_team_slug' => $classification['team'],
                'file_type' => $classification['file_type'],
                'content_hash' => hash('sha256', $content),
                'status' => 'queued',
            ]);
        }
    }

    public function startBatch(LegacyImportBatch $batch): void
    {
        if ($batch->status !== 'queued' && $batch->status !== 'failed') {
            return;
        }

        $batch->update([
            'status' => 'queued',
            'started_at' => now(),
            'processed_files' => 0,
            'success_files' => 0,
            'failed_files' => 0,
        ]);

        \App\Jobs\Stats\Legacy\ProcessLegacyImportBatchJob::dispatch($batch->id);
    }
}
