<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\BasketballMatch;
use App\Models\PlayerProfile;
use App\Models\Team;
use App\Models\Training;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminKpiOverview extends BaseWidget
{
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
            Stat::make(__('admin/dashboard.kpi.users_total'), $users)
                ->description(__('admin/dashboard.kpi.users_active_desc', ['count' => $activeUsers]))
                ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::USERS))
                ->color('primary'),
            Stat::make(__('admin/dashboard.kpi.players_total'), $players)
                ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::PLAYER_PROFILES))
                ->color('success'),
            Stat::make(__('admin/dashboard.kpi.teams_total'), $teams)
                ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::TEAMS))
                ->color('warning'),
            Stat::make(__('admin/dashboard.kpi.matches_total'), $matchesTotal)
                ->description(__('admin/dashboard.kpi.matches_upcoming_desc', ['count' => $matchesUpcoming]))
                ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::MATCHES))
                ->color('info'),
            Stat::make(__('admin/dashboard.kpi.trainings_total'), $trainingsTotal)
                ->description(__('admin/dashboard.kpi.trainings_upcoming_desc', ['count' => $trainingsUpcoming]))
                ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::TRAININGS))
                ->color('info'),
            Stat::make(__('admin/dashboard.kpi.attendance_total'), $attendanceTotal)
                ->description(__('admin/dashboard.kpi.attendance_desc'))
                ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::CHECK_CIRCLE))
                ->color('gray'),
        ];
    }
}
