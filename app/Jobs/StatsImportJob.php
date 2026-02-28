<?php

namespace App\Jobs;

use App\Models\ExternalStatSource;
use App\Services\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class StatsImportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(AuditLogService $auditLogService): void
    {
        Log::info('Stats Import Job started.');

        $auditLogService->log(
            eventKey: 'stats.import_started',
            category: 'system',
            action: 'import_started',
            severity: 'info'
        );

        $sources = ExternalStatSource::where('is_active', true)->get();

        if ($sources->isEmpty()) {
            Log::info('No active stat sources found. Skipping import.');

            $auditLogService->log(
                eventKey: 'stats.import_skipped',
                category: 'system',
                action: 'import_skipped',
                metadata: ['reason' => 'No active sources'],
                severity: 'info'
            );

            return;
        }

        foreach ($sources as $source) {
            Log::info("Processing stat source: {$source->name} (URL: {$source->source_url})");

            $auditLogService->log(
                eventKey: 'stats.source_processing',
                category: 'system',
                action: 'processing',
                subject: $source,
                severity: 'info'
            );
            // Tady by byla budoucí pipeline: Fetch -> Extract -> Normalize -> Import
            // Zatím pouze skeleton placeholder
        }

        Log::info('Stats Import Job finished.');

        $auditLogService->log(
            eventKey: 'stats.import_finished',
            category: 'system',
            action: 'import_finished',
            metadata: ['sources_count' => $sources->count()],
            severity: 'info'
        );
    }
}
