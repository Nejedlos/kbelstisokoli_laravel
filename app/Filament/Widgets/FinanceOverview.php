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
            Stat::make('Pohledávky celkem', number_format($summary['total_receivables'], 0, ',', ' ') . ' Kč')
                ->description('Otevřené a částečně zaplacené předpisy')
                ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::BANKNOTES))
                ->color('info'),
            Stat::make('Po splatnosti', number_format($summary['total_overdue'], 0, ',', ' ') . ' Kč')
                ->description('Předpisy po termínu splatnosti')
                ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::CLOCK))
                ->color('danger'),
            Stat::make('Příjmy (tento měsíc)', number_format($summary['payments_received_month'], 0, ',', ' ') . ' Kč')
                ->description('Celkem přijaté platby v tomto měsíci')
                ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::CHECK_CIRCLE))
                ->color('success'),
        ];
    }
}
