<?php

namespace App\Filament\Widgets;

use App\Services\Finance\FinanceService;
use App\Support\IconHelper;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class FinanceOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $service = app(FinanceService::class);
        $summary = $service->getAdminSummary();

        return [
            Stat::make(new HtmlString(IconHelper::render(IconHelper::BANKNOTES) . ' ' . __('admin/dashboard.finance.total_receivables')), number_format($summary['total_receivables'], 0, ',', ' ').' Kč')
                ->description(__('admin/dashboard.finance.total_receivables_desc'))
                ->color('info'),
            Stat::make(new HtmlString(IconHelper::render(IconHelper::CLOCK) . ' ' . __('admin/dashboard.finance.overdue')), number_format($summary['total_overdue'], 0, ',', ' ').' Kč')
                ->description(__('admin/dashboard.finance.overdue_desc'))
                ->color('danger'),
            Stat::make(new HtmlString(IconHelper::render(IconHelper::CHECK_CIRCLE) . ' ' . __('admin/dashboard.finance.payments_month')), number_format($summary['payments_received_month'], 0, ',', ' ').' Kč')
                ->description(__('admin/dashboard.finance.payments_month_desc'))
                ->color('success'),
        ];
    }
}
