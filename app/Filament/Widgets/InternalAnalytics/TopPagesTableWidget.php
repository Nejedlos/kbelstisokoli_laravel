<?php

namespace App\Filament\Widgets\InternalAnalytics;

use App\Models\InternalAnalyticsEvent;
use App\Services\InternalAnalytics\AnalyticsQueryService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

class TopPagesTableWidget extends BaseWidget
{
    public array $filters = [];

    #[On('filtersUpdated')]
    public function updateFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    public function getHeading(): string
    {
        return __('internal-analytics.tables.top_pages');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->applyFilters(InternalAnalyticsEvent::query())
                    ->select(
                        'path',
                        'route_name',
                        'area',
                        DB::raw('COUNT(*) as views'),
                        DB::raw('COUNT(DISTINCT visitor_hash) as unique_visitors'),
                        DB::raw('AVG(response_time_ms) as avg_response_time')
                    )
                    ->where('event_type', 'page_view')
                    ->groupBy('path', 'route_name', 'area')
                    ->orderByDesc('views')
            )
            ->columns([
                Tables\Columns\TextColumn::make('path')
                    ->label(__('internal-analytics.tables.column.path'))
                    ->limit(50),
                Tables\Columns\TextColumn::make('area')
                    ->label(__('internal-analytics.tables.column.area'))
                    ->badge(),
                Tables\Columns\TextColumn::make('views')
                    ->label(__('internal-analytics.tables.column.views'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unique_visitors')
                    ->label(__('internal-analytics.tables.column.unique_visitors'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('avg_response_time')
                    ->label(__('internal-analytics.tables.column.avg_time'))
                    ->suffix(' ms')
                    ->numeric(0)
                    ->sortable(),
            ])
            ->paginated([5, 10]);
    }

    protected function applyFilters($query)
    {
        $filters = $this->filters;
        $dateFrom = null;

        switch ($filters['period'] ?? 'last_7_days') {
            case 'today': $dateFrom = now()->startOfDay(); break;
            case 'yesterday': $dateFrom = now()->subDay()->startOfDay(); break;
            case 'last_30_days': $dateFrom = now()->subDays(30); break;
            case 'this_month': $dateFrom = now()->startOfMonth(); break;
            case 'last_month': $dateFrom = now()->subMonth()->startOfMonth(); break;
            case 'last_7_days': default: $dateFrom = now()->subDays(7); break;
        }

        if ($dateFrom) {
            $query->where('occurred_at', '>=', $dateFrom);
        }

        if (isset($filters['area']) && $filters['area'] !== 'all') {
            $query->where('area', $filters['area']);
        }

        return $query;
    }
}
