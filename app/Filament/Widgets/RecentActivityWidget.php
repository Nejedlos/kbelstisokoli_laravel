<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Widgets\Widget;

class RecentActivityWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-activity-widget';

    protected static ?int $sort = -170;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $items = collect();
        if (class_exists(AuditLog::class)) {
            try {
                $items = AuditLog::query()->latest('occurred_at')->latest('created_at')->limit(3)->get();
            } catch (\Throwable $e) {
                $items = collect();
            }
        }

        return [
            'items' => $items,
        ];
    }
}
