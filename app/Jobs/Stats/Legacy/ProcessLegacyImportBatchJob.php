<?php

namespace App\Jobs\Stats\Legacy;

use App\Models\LegacyImportBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessLegacyImportBatchJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $batchId
    ) {}

    public function handle(): void
    {
        $batch = LegacyImportBatch::findOrFail($this->batchId);

        if ($batch->status === 'success') {
            return;
        }

        $batch->update(['status' => 'running']);

        $files = $batch->files()->where('status', 'queued')->get();

        foreach ($files as $file) {
            ProcessLegacyImportFileJob::dispatch($file->id);
        }
    }
}
