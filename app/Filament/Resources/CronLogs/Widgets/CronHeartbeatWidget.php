<?php

namespace App\Filament\Resources\CronLogs\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use App\Support\IconHelper;
use Illuminate\Support\HtmlString;

class CronHeartbeatWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $lastHeartbeat = Cache::get('scheduler_heartbeat');
        if (! $lastHeartbeat) {
            $lastHeartbeat = Cache::store('file')->get('scheduler_heartbeat');
        }

        if (is_string($lastHeartbeat)) {
            try {
                $lastHeartbeat = Carbon::parse($lastHeartbeat);
            } catch (\Exception $e) {
                $lastHeartbeat = null;
            }
        }

        $isOk = $lastHeartbeat && $lastHeartbeat instanceof Carbon && $lastHeartbeat->diffInMinutes(now()) < 5;

        return [
            Stat::make(
                new HtmlString(IconHelper::render(IconHelper::HEARTBEAT) . ' ' . __('admin.widgets.cron_heartbeat.label')),
                $isOk ? __('admin.widgets.cron_heartbeat.running') : __('admin.widgets.cron_heartbeat.inactive')
            )
                ->description($lastHeartbeat instanceof Carbon ? __('admin.widgets.cron_heartbeat.last_run', ['time' => $lastHeartbeat->diffForHumans()]) : __('admin.widgets.cron_heartbeat.no_heartbeat'))
                ->color($isOk ? 'success' : 'danger'),
        ];
    }
}
