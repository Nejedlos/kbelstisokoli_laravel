<?php

namespace App\Jobs;

use App\Models\CronLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class MaintenanceCleanupJob implements ShouldQueue
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
        Log::info('Maintenance Cleanup Job started.');

        // Smazání cron logů starších než 30 dní
        $retentionDays = config('system.cron.log_retention_days', 30);
        $deletedLogsCount = CronLog::where('started_at', '<', now()->subDays($retentionDays))->delete();

        Log::info("Maintenance Cleanup Job finished. Deleted {$deletedLogsCount} old cron logs.");
    }
}
