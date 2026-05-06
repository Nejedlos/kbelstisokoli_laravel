<?php

namespace App\Filament\Widgets\InternalAnalytics;

use App\Models\InternalAnalyticsEvent;
use App\Services\InternalAnalytics\AnalyticsQueryService;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Livewire\Attributes\On;
use Illuminate\Support\Carbon;

class RecentEventsTableWidget extends BaseWidget
{
    public array $filters = [];

    #[On('filtersUpdated')]
    public function updateFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    public function getHeading(): string
    {
        return __('internal-analytics.tables.recent_events');
    }

    public function table(Table $table): Table
    {
        $queryService = app(AnalyticsQueryService::class);

        return $table
            ->query(
                $this->applyFilters(InternalAnalyticsEvent::query())
                    ->with('user')
                    ->latest('occurred_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')
                    ->label(__('internal-analytics.tables.column.occurred_at'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event_type')
                    ->label(__('internal-analytics.tables.column.event_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'page_view' => 'gray',
                        'login_success' => 'success',
                        'login_failed' => 'danger',
                        'logout' => 'warning',
                        'error_request' => 'danger',
                        'slow_request' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('area')
                    ->label(__('internal-analytics.tables.column.area'))
                    ->badge(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('internal-analytics.tables.column.user'))
                    ->placeholder('Host'),
                Tables\Columns\TextColumn::make('path')
                    ->label(__('internal-analytics.tables.column.path'))
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->path),
                Tables\Columns\TextColumn::make('status_code')
                    ->label(__('internal-analytics.tables.column.status'))
                    ->numeric()
                    ->color(fn ($state) => $state >= 400 ? 'danger' : ($state >= 300 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('response_time_ms')
                    ->label(__('internal-analytics.tables.column.time'))
                    ->suffix(' ms')
                    ->numeric(),
            ])
            ->paginated([5, 10, 25]);
    }

    protected function applyFilters($query)
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

        if ($dateFrom) {
            $query->where('occurred_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('occurred_at', '<=', $dateTo);
        }

        if (isset($filters['area']) && $filters['area'] !== 'all') {
            $query->where('area', $filters['area']);
        }

        if (isset($filters['authenticated']) && $filters['authenticated'] !== 'all') {
            $query->where('is_authenticated', $filters['authenticated'] === 'yes');
        }

        return $query;
    }
}
