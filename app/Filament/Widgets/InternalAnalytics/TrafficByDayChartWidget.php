<?php

namespace App\Filament\Widgets\InternalAnalytics;

use App\Services\InternalAnalytics\AnalyticsQueryService;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class TrafficByDayChartWidget extends ChartWidget
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
        return __('internal-analytics.charts.traffic_by_day');
    }

    protected function getData(): array
    {
        $queryService = app(AnalyticsQueryService::class);
        $data = $queryService->getTrafficByDay($this->prepareFilters());

        return [
            'datasets' => [
                [
                    'label' => 'Všechny requesty',
                    'data' => $data['datasets'][0]['data'],
                    'borderColor' => '#3b82f6',
                ],
                [
                    'label' => 'Unikátní návštěvníci',
                    'data' => $data['datasets'][1]['data'],
                    'borderColor' => '#10b981',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function prepareFilters(): array
    {
        $filters = $this->filters;
        $dateFrom = now()->subDays(7);
        $dateTo = now();

        switch ($filters['period'] ?? 'last_7_days') {
            case 'today':
                $dateFrom = now()->startOfDay();
                break;
            case 'yesterday':
                $dateFrom = now()->subDay()->startOfDay();
                $dateTo = now()->subDay()->endOfDay();
                break;
            case 'last_30_days':
                $dateFrom = now()->subDays(30);
                break;
            case 'this_month':
                $dateFrom = now()->startOfMonth();
                break;
            case 'last_month':
                $dateFrom = now()->subMonth()->startOfMonth();
                $dateTo = now()->subMonth()->endOfMonth();
                break;
            case 'last_7_days':
            default:
                $dateFrom = now()->subDays(7);
                break;
        }

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'area' => $filters['area'] ?? 'all',
            'authenticated' => $filters['authenticated'] ?? 'all',
        ];
    }
}
