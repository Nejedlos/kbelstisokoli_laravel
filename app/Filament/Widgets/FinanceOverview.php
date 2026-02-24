<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Services\Finance\FinanceService;

class FinanceOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $service = app(FinanceService::class);
        $summary = $service->getAdminSummary();

        return [
            Stat::make(__('admin/dashboard.finance.total_receivables'), number_format($summary['total_receivables'], 0, ',', ' ') . ' Kč')
                ->description(__('admin/dashboard.finance.total_receivables_desc'))
                ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::BANKNOTES))
                ->color('info'),
            Stat::make(__('admin/dashboard.finance.overdue'), number_format($summary['total_overdue'], 0, ',', ' ') . ' Kč')
                ->description(__('admin/dashboard.finance.overdue_desc'))
                ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::CLOCK))
                ->color('danger'),
            Stat::make(__('admin/dashboard.finance.payments_month'), number_format($summary['payments_received_month'], 0, ',', ' ') . ' Kč')
                ->description(__('admin/dashboard.finance.payments_month_desc'))
                ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::CHECK_CIRCLE))
                ->color('success'),
        ];
    }
}
