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
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Maintenance Cleanup Job started.');

        // 1. Cron logy (retence dle configu, výchozí 30 dní)
        $retentionDays = config('system.cron.log_retention_days', 30);
        $deletedLogsCount = CronLog::where('started_at', '<', now()->subDays($retentionDays))->delete();

        // 2. Audit logy (retence 90 dní)
        $deletedAuditCount = \App\Models\AuditLog::where('occurred_at', '<', now()->subDays(90))->delete();

        // 3. Not Found logy (retence 30 dní)
        $deletedNotFoundCount = \App\Models\NotFoundLog::where('created_at', '<', now()->subDays(30))->delete();

        // 4. Externí importy (retence 30 dní)
        $deletedImportRuns = \App\Models\ExternalImportRun::where('created_at', '<', now()->subDays(30))->delete();
        // Logy k nim se smažou kaskádou v DB nebo přes model events (pokud jsou nastaveny)

        // 5. Staré sezení v databázi (pokud se používá database driver)
        if (config('session.driver') === 'database') {
            $deletedSessions = \Illuminate\Support\Facades\DB::table(config('session.table', 'sessions'))
                ->where('last_activity', '<', now()->subMinutes(config('session.lifetime'))->getTimestamp())
                ->delete();
        }

        Log::info("Maintenance Cleanup Job finished.", [
            'cron_logs_deleted' => $deletedLogsCount,
            'audit_logs_deleted' => $deletedAuditCount,
            'not_found_logs_deleted' => $deletedNotFoundCount,
            'import_runs_deleted' => $deletedImportRuns,
            'sessions_deleted' => $deletedSessions ?? 0,
        ]);
    }
}
