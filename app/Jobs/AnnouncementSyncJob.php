<?php

namespace App\Jobs;

use App\Models\Announcement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AnnouncementSyncJob implements ShouldQueue
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
        Log::info('Announcement Sync Job started.');

        // Deaktivace expirovaných oznámení
        $expiredCount = Announcement::where('is_active', true)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->update(['is_active' => false]);

        Log::info("Announcement Sync Job finished. Deactivated {$expiredCount} expired announcements.");
    }
}
