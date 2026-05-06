<?php

namespace App\Services\InternalAnalytics;

use App\Models\InternalAnalyticsEvent;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsQueryService
{
    public function getOverviewStats(array $filters): array
    {
        $query = $this->applyFilters(InternalAnalyticsEvent::query(), $filters);

        return [
            'page_views' => (clone $query)->where('event_type', 'page_view')->count(),
            'unique_visitors' => (clone $query)->distinct('visitor_hash')->count('visitor_hash'),
            'authenticated_users' => (clone $query)->where('is_authenticated', true)->distinct('user_id')->count('user_id'),
            'logins' => (clone $query)->where('event_type', 'login_success')->count(),
            'admin_access' => (clone $query)->where('area', 'admin')->count(),
            'member_access' => (clone $query)->where('area', 'member')->count(),
            'error_requests' => (clone $query)->where('event_type', 'error_request')->count(),
            'avg_response_time' => (clone $query)->avg('response_time_ms') ?? 0,
            'max_response_time' => (clone $query)->max('response_time_ms') ?? 0,
            'not_found_count' => (clone $query)->where('event_type', 'not_found_request')->count(),
        ];
    }

    public function getTrafficByDay(array $filters): array
    {
        $query = $this->applyFilters(InternalAnalyticsEvent::query(), $filters);

        $results = $query->select(
            DB::raw('DATE(occurred_at) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('COUNT(DISTINCT visitor_hash) as unique_visitors')
        )
        ->whereIn('event_type', ['page_view', 'request'])
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return [
            'labels' => $results->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d.m.'))->toArray(),
            'datasets' => [
                [
                    'label' => 'Celkem',
                    'data' => $results->pluck('total')->toArray(),
                ],
                [
                    'label' => 'Unikátní',
                    'data' => $results->pluck('unique_visitors')->toArray(),
                ],
            ],
        ];
    }

    public function getTrafficByArea(array $filters): array
    {
        $query = $this->applyFilters(InternalAnalyticsEvent::query(), $filters);

        $results = $query->select('area', DB::raw('COUNT(*) as total'))
            ->whereIn('event_type', ['page_view', 'request'])
            ->groupBy('area')
            ->get();

        return [
            'labels' => $results->pluck('area')->toArray(),
            'datasets' => [
                [
                    'data' => $results->pluck('total')->toArray(),
                ],
            ],
        ];
    }

    public function getLoginsByDay(array $filters): array
    {
        $query = $this->applyFilters(InternalAnalyticsEvent::query(), $filters);

        $results = $query->select(
            DB::raw('DATE(occurred_at) as date'),
            DB::raw('COUNT(*) as total')
        )
        ->where('event_type', 'login_success')
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return [
            'labels' => $results->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d.m.'))->toArray(),
            'datasets' => [
                [
                    'label' => 'Přihlášení',
                    'data' => $results->pluck('total')->toArray(),
                ],
            ],
        ];
    }

    public function getStatusCodeDistribution(array $filters): array
    {
        $query = $this->applyFilters(InternalAnalyticsEvent::query(), $filters);

        $results = $query->select(
            DB::raw('CASE
                WHEN status_code < 300 THEN "2xx"
                WHEN status_code < 400 THEN "3xx"
                WHEN status_code < 500 THEN "4xx"
                ELSE "5xx"
            END as group_name'),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('group_name')
        ->get();

        return [
            'labels' => $results->pluck('group_name')->toArray(),
            'datasets' => [
                [
                    'data' => $results->pluck('total')->toArray(),
                ],
            ],
        ];
    }

    public function getTopPages(array $filters, int $limit = 10): \Illuminate\Support\Collection
    {
        $query = $this->applyFilters(InternalAnalyticsEvent::query(), $filters);

        return $query->select(
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
        ->limit($limit)
        ->get();
    }

    public function getRecentEvents(array $filters, int $limit = 20): \Illuminate\Support\Collection
    {
        $query = $this->applyFilters(InternalAnalyticsEvent::query(), $filters);

        return $query->with('user')
            ->orderByDesc('occurred_at')
            ->limit($limit)
            ->get();
    }

    public function getSlowRequests(array $filters, int $limit = 10): \Illuminate\Support\Collection
    {
        $query = $this->applyFilters(InternalAnalyticsEvent::query(), $filters);

        return $query->with('user')
            ->where('event_type', 'slow_request')
            ->orderByDesc('response_time_ms')
            ->limit($limit)
            ->get();
    }

    public function getErrorRequests(array $filters, int $limit = 10): \Illuminate\Support\Collection
    {
        $query = $this->applyFilters(InternalAnalyticsEvent::query(), $filters);

        return $query->with('user')
            ->whereIn('event_type', ['error_request', 'not_found_request'])
            ->orderByDesc('occurred_at')
            ->limit($limit)
            ->get();
    }

    public function getGuestVsAuthenticated(array $filters): array
    {
        $query = $this->applyFilters(InternalAnalyticsEvent::query(), $filters);

        $results = $query->select(
            'is_authenticated',
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('is_authenticated')
        ->get();

        return [
            'labels' => ['Hosté', 'Přihlášení'],
            'datasets' => [
                [
                    'data' => [
                        $results->where('is_authenticated', false)->first()?->total ?? 0,
                        $results->where('is_authenticated', true)->first()?->total ?? 0,
                    ],
                ],
            ],
        ];
    }

    protected function applyFilters($query, array $filters)
    {
        if (isset($filters['date_from'])) {
            $query->where('occurred_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('occurred_at', '<=', $filters['date_to']);
        }

        if (isset($filters['area']) && $filters['area'] !== 'all') {
            $query->where('area', $filters['area']);
        }

        if (isset($filters['event_type']) && $filters['event_type'] !== 'all') {
            $query->where('event_type', $filters['event_type']);
        }

        if (isset($filters['authenticated']) && $filters['authenticated'] !== 'all') {
            $query->where('is_authenticated', $filters['authenticated'] === 'yes');
        }

        return $query;
    }
}
