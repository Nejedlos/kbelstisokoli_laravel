<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\BasketballMatch;
use App\Models\PlayerProfile;
use App\Models\Team;
use App\Models\Training;
use App\Models\User;
use App\Support\IconHelper;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;

class AdminKpiOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()->can('access_admin');
    }

    protected static ?int $sort = -190;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected function getColumns(): int|array
    {
        return [
            'md' => 2,
            'lg' => 3,
        ];
    }

    protected function getStats(): array
    {
        $users = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $players = PlayerProfile::count();
        $teams = Team::count();
        $matchesTotal = BasketballMatch::count();
        $matchesUpcoming = BasketballMatch::where('scheduled_at', '>=', now())->count();
        $trainingsTotal = Training::count();
        $trainingsUpcoming = Training::where('starts_at', '>=', now())->count();
        $attendanceTotal = class_exists(Attendance::class) ? Attendance::count() : 0;

        return [
            Stat::make(new HtmlString(IconHelper::render(IconHelper::USERS) . ' ' . __('admin/dashboard.kpi.users_total')), $users)
                ->description(__('admin/dashboard.kpi.users_active_desc', ['count' => $activeUsers]))
                ->color('primary'),
            Stat::make(new HtmlString(IconHelper::render(IconHelper::PLAYER_PROFILES) . ' ' . __('admin/dashboard.kpi.players_total')), $players)
                ->color('success'),
            Stat::make(new HtmlString(IconHelper::render(IconHelper::TEAMS) . ' ' . __('admin/dashboard.kpi.teams_total')), $teams)
                ->color('warning'),
            Stat::make(new HtmlString(IconHelper::render(IconHelper::MATCHES) . ' ' . __('admin/dashboard.kpi.matches_total')), $matchesTotal)
                ->description(__('admin/dashboard.kpi.matches_upcoming_desc', ['count' => $matchesUpcoming]))
                ->color('info'),
            Stat::make(new HtmlString(IconHelper::render(IconHelper::TRAININGS) . ' ' . __('admin/dashboard.kpi.trainings_total')), $trainingsTotal)
                ->description(__('admin/dashboard.kpi.trainings_upcoming_desc', ['count' => $trainingsUpcoming]))
                ->color('info'),
            Stat::make(new HtmlString(IconHelper::render(IconHelper::CHECK_CIRCLE) . ' ' . __('admin/dashboard.kpi.attendance_total')), $attendanceTotal)
                ->description(__('admin/dashboard.kpi.attendance_desc'))
                ->color('gray'),
        ];
    }
}
