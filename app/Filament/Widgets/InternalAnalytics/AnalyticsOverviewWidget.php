<?php

namespace App\Filament\Widgets\InternalAnalytics;

use App\Services\InternalAnalytics\AnalyticsQueryService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;
use Carbon\Carbon;
use App\Support\Icons\AppIcon;
use App\Support\FilamentIcon;
use Illuminate\Support\HtmlString;

class AnalyticsOverviewWidget extends BaseWidget
{
    public array $filters = [];

    #[On('filtersUpdated')]
    public function updateFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    protected function getStats(): array
    {
        $queryService = app(AnalyticsQueryService::class);
        $data = $queryService->getOverviewStats($this->prepareFilters());

        return [
            Stat::make(new HtmlString(FilamentIcon::render(AppIcon::VIEW) . ' ' . __('internal-analytics.stats.page_views')), $data['page_views']),
            Stat::make(new HtmlString(FilamentIcon::render(AppIcon::USERS) . ' ' . __('internal-analytics.stats.unique_visitors')), $data['unique_visitors']),
            Stat::make(new HtmlString(FilamentIcon::render(AppIcon::USER) . ' ' . __('internal-analytics.stats.authenticated_users')), $data['authenticated_users']),
            Stat::make(new HtmlString(FilamentIcon::render(AppIcon::PERMISSIONS) . ' ' . __('internal-analytics.stats.logins')), $data['logins']),
            Stat::make(new HtmlString(FilamentIcon::render(AppIcon::CRON_TASKS) . ' ' . __('internal-analytics.stats.avg_response_time')), round($data['avg_response_time']) . ' ms')
                ->color($data['avg_response_time'] > 500 ? 'warning' : 'success'),
            Stat::make(new HtmlString(FilamentIcon::render(AppIcon::WARNING) . ' ' . __('internal-analytics.stats.error_requests')), $data['error_requests'])
                ->color($data['error_requests'] > 0 ? 'danger' : 'success'),
        ];
    }

    protected function prepareFilters(): array
    {
        $filters = $this->filters;
        $dateFrom = null;
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
