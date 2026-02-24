<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class SystemHealthWidget extends Widget
{
    protected string $view = 'filament.widgets.system-health-widget';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $cronOk = false;
        $lastRunHuman = __('admin/dashboard.system.cron.unknown');

        // Cron last run - best effort
        $lastRun = null;
        if (class_exists(\App\Models\CronLog::class)) {
            try {
                $last = \App\Models\CronLog::query()->latest('created_at')->first();
                if ($last) {
                    $lastRun = $last->created_at ?? $last->occurred_at ?? null;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }
        if ($lastRun) {
            $cronOk = now()->diffInMinutes($lastRun) <= 15; // arbitrary 15m window
            $lastRunHuman = __('admin/dashboard.system.cron.last_run', ['time' => $lastRun->diffForHumans()]);
        }

        // AI index status - best effort
        $aiReady = false;
        if (class_exists(\App\Models\AiSetting::class)) {
            try {
                $ai = \App\Models\AiSetting::first();
                $aiReady = (bool)($ai?->enabled ?? config('ai.enabled', true));
            } catch (\Throwable $e) {
                $aiReady = (bool) config('ai.enabled', true);
            }
        } else {
            $aiReady = (bool) config('ai.enabled', true);
        }

        return [
            'cronOk' => $cronOk,
            'lastRunHuman' => $lastRunHuman,
            'aiReady' => $aiReady,
        ];
    }
}
