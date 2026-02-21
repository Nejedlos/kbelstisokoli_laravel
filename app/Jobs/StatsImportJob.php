<?php

namespace App\Jobs;

use App\Models\ExternalStatSource;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class StatsImportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Stats Import Job started.');

        $sources = ExternalStatSource::where('is_active', true)->get();

        if ($sources->isEmpty()) {
            Log::info('No active stat sources found. Skipping import.');
            return;
        }

        foreach ($sources as $source) {
            Log::info("Processing stat source: {$source->name} (URL: {$source->source_url})");
            // Tady by byla budoucí pipeline: Fetch -> Extract -> Normalize -> Import
            // Zatím pouze skeleton placeholder
        }

        Log::info('Stats Import Job finished.');
    }
}
