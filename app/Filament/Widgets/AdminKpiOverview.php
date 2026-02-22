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
                ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::USERS))
                ->color('primary'),
            Stat::make('Hráčské profily', $players)
                ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::PLAYER_PROFILES))
                ->color('success'),
            Stat::make('Týmy', $teams)
                ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::TEAMS))
                ->color('warning'),
            Stat::make('Zápasy', $matchesTotal)
                ->description("Nadcházející: {$matchesUpcoming}")
                ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::MATCHES))
                ->color('info'),
            Stat::make('Tréninky', $trainingsTotal)
                ->description("Nadcházející: {$trainingsUpcoming}")
                ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::TRAININGS))
                ->color('info'),
            Stat::make('RSVP/Docházka', $attendanceTotal)
                ->description('Počet záznamů (placeholder)')
                ->icon(\App\Support\FilamentIcon::get(\App\Support\FilamentIcon::CHECK_CIRCLE))
                ->color('gray'),
        ];
    }
}
