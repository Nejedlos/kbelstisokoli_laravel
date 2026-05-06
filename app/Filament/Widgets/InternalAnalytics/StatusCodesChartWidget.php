<?php

namespace App\Filament\Widgets\InternalAnalytics;

use App\Services\InternalAnalytics\AnalyticsQueryService;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class StatusCodesChartWidget extends ChartWidget
{
    public array $filters = [];

    #[On('filtersUpdated')]
    public function updateFilters(array $filters): void
    {
        $this->filters = $filters;
        $this->updateChartData();
    }

    public function getHeading(): string
    {
        return __('internal-analytics.charts.status_codes');
    }

    protected function getData(): array
    {
        $queryService = app(AnalyticsQueryService::class);
        $data = $queryService->getStatusCodeDistribution($this->prepareFilters());

        return [
            'datasets' => [
                [
                    'label' => __('internal-analytics.charts.status_codes'),
                    'data' => $data['datasets'][0]['data'],
                    'backgroundColor' => ['#22c55e', '#f59e0b', '#ef4444', '#991b1b'],
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function prepareFilters(): array
    {
        $filters = $this->filters;
        $dateFrom = now()->subDays(7);
        $dateTo = now();

        switch ($filters['period'] ?? 'last_7_days') {
            case 'today': $dateFrom = now()->startOfDay(); break;
            case 'yesterday': $dateFrom = now()->subDay()->startOfDay(); $dateTo = now()->subDay()->endOfDay(); break;
            case 'last_30_days': $dateFrom = now()->subDays(30); break;
            case 'this_month': $dateFrom = now()->startOfMonth(); break;
            case 'last_month': $dateFrom = now()->subMonth()->startOfMonth(); $dateTo = now()->subMonth()->endOfMonth(); break;
            case 'last_7_days': default: $dateFrom = now()->subDays(7); break;
        }

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'area' => $filters['area'] ?? 'all',
            'authenticated' => $filters['authenticated'] ?? 'all',
        ];
    }
}
