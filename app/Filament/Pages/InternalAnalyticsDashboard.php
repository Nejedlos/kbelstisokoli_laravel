<?php

namespace App\Filament\Pages;

use App\Services\InternalAnalytics\AnalyticsQueryService;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use App\Support\Icons\AppIcon;
use App\Support\FilamentIcon;
use Filament\Pages\Dashboard\Concerns\HasFilters;

class InternalAnalyticsDashboard extends Page
{
    use HasFilters;

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return FilamentIcon::get(AppIcon::GAUGE);
    }

    protected string $view = 'filament.pages.internal-analytics-dashboard';

    public ?array $tableFilters = [
        'period' => 'last_7_days',
        'area' => 'all',
        'authenticated' => 'all',
    ];

    public function getTitle(): string
    {
        return __('internal-analytics.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('internal-analytics.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('internal-analytics.navigation_group');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function mount(): void
    {
        if (!auth()->user()?->hasRole('admin')) {
            abort(403);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('filter')
                ->label('Filtry')
                ->icon(FilamentIcon::get(AppIcon::FILTER))
                ->form([
                    Grid::make(3)
                        ->schema([
                            Select::make('period')
                                ->label(__('internal-analytics.filters.period'))
                                ->options([
                                    'today' => __('internal-analytics.filters.today'),
                                    'yesterday' => __('internal-analytics.filters.yesterday'),
                                    'last_7_days' => __('internal-analytics.filters.last_7_days'),
                                    'last_30_days' => __('internal-analytics.filters.last_30_days'),
                                    'this_month' => __('internal-analytics.filters.this_month'),
                                    'last_month' => __('internal-analytics.filters.last_month'),
                                ])
                                ->default('last_7_days'),
                            Select::make('area')
                                ->label(__('internal-analytics.filters.area'))
                                ->options([
                                    'all' => __('internal-analytics.filters.all'),
                                    'frontend' => __('internal-analytics.filters.frontend'),
                                    'member' => __('internal-analytics.filters.member'),
                                    'admin' => __('internal-analytics.filters.admin'),
                                    'api' => __('internal-analytics.filters.api'),
                                ])
                                ->default('all'),
                            Select::make('authenticated')
                                ->label(__('internal-analytics.filters.authenticated'))
                                ->options([
                                    'all' => __('internal-analytics.filters.all'),
                                    'no' => __('internal-analytics.filters.guests'),
                                    'yes' => __('internal-analytics.filters.authenticated_only'),
                                ])
                                ->default('all'),
                        ]),
                ])
                ->action(function (array $data) {
                    $this->tableFilters = $data;
                    $this->dispatch('filtersUpdated', filters: $data);
                })
                ->fillForm($this->tableFilters),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\InternalAnalytics\AnalyticsOverviewWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\InternalAnalytics\TrafficByDayChartWidget::class,
            \App\Filament\Widgets\InternalAnalytics\TopPagesTableWidget::class,
            \App\Filament\Widgets\InternalAnalytics\RecentEventsTableWidget::class,
        ];
    }
}
