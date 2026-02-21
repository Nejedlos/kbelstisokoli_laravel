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
            Stat::make('Uživatelé (celkem)', $users)
                ->description("Aktivních: {$activeUsers}")
                ->color('primary'),
            Stat::make('Hráčské profily', $players)
                ->color('success'),
            Stat::make('Týmy', $teams)
                ->color('warning'),
            Stat::make('Zápasy', $matchesTotal)
                ->description("Nadcházející: {$matchesUpcoming}")
                ->color('info'),
            Stat::make('Tréninky', $trainingsTotal)
                ->description("Nadcházející: {$trainingsUpcoming}")
                ->color('info'),
            Stat::make('RSVP/Docházka', $attendanceTotal)
                ->description('Počet záznamů (placeholder)')
                ->color('gray'),
        ];
    }
}
