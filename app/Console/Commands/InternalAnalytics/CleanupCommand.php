<?php

namespace App\Console\Commands\InternalAnalytics;

use App\Models\InternalAnalyticsEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CleanupCommand extends Command
{
    protected $signature = 'internal-analytics:cleanup {--dry-run}';

    protected $description = 'Smaže stará data z interní analytiky.';

    public function handle(): void
    {
        $retentionDays = config('internal-analytics.retention_days', 90);
        $date = Carbon::now()->subDays($retentionDays);

        $query = InternalAnalyticsEvent::where('occurred_at', '<', $date);
        $count = $query->count();

        if ($this->option('dry-run')) {
            $this->info("Bylo by smazáno {$count} záznamů starších než {$date->toDateString()}.");
            return;
        }

        $query->delete();
        $this->info("Smazáno {$count} záznamů starších než {$date->toDateString()}.");
    }
}
