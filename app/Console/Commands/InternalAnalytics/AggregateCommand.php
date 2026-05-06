<?php

namespace App\Console\Commands\InternalAnalytics;

use App\Models\InternalAnalyticsEvent;
use App\Models\InternalAnalyticsDailySummary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AggregateCommand extends Command
{
    protected $signature = 'internal-analytics:aggregate {--date= : Datum ve formátu YYYY-MM-DD} {--force}';

    protected $description = 'Agreguje data z interní analytiky do denních souhrnů.';

    public function handle(): void
    {
        $dateStr = $this->option('date') ?: Carbon::yesterday()->toDateString();
        $date = Carbon::parse($dateStr);

        $this->info("Agreguji data pro den {$date->toDateString()}...");

        $results = InternalAnalyticsEvent::whereDate('occurred_at', $date)
            ->select(
                'area',
                'event_type',
                DB::raw('COUNT(*) as total_count'),
                DB::raw('COUNT(DISTINCT visitor_hash) as unique_visitors'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users'),
                DB::raw('AVG(response_time_ms) as avg_response_time'),
                DB::raw('MAX(response_time_ms) as max_response_time'),
                DB::raw('SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as status_2xx'),
                DB::raw('SUM(CASE WHEN status_code >= 300 AND status_code < 400 THEN 1 ELSE 0 END) as status_3xx'),
                DB::raw('SUM(CASE WHEN status_code >= 400 AND status_code < 500 THEN 1 ELSE 0 END) as status_4xx'),
                DB::raw('SUM(CASE WHEN status_code >= 500 THEN 1 ELSE 0 END) as status_5xx')
            )
            ->groupBy('area', 'event_type')
            ->get();

        if ($results->isEmpty()) {
            $this->warn("Pro datum {$date->toDateString()} nebyla nalezena žádná data.");
            return;
        }

        foreach ($results as $row) {
            InternalAnalyticsDailySummary::updateOrCreate(
                [
                    'date' => $date->toDateString(),
                    'area' => $row->area,
                    'event_type' => $row->event_type,
                ],
                [
                    'total_count' => $row->total_count,
                    'unique_visitors' => $row->unique_visitors,
                    'unique_users' => $row->unique_users,
                    'avg_response_time_ms' => (int) $row->avg_response_time,
                    'max_response_time_ms' => $row->max_response_time,
                    'status_2xx_count' => $row->status_2xx,
                    'status_3xx_count' => $row->status_3xx,
                    'status_4xx_count' => $row->status_4xx,
                    'status_5xx_count' => $row->status_5xx,
                ]
            );
        }

        $this->info("Agregace pro {$date->toDateString()} dokončena.");
    }
}
