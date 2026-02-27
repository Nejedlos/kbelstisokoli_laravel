<?php

return [
    // Veřejná navigace (hlavní menu)
    'public' => [
        ['title' => 'nav.home', 'route' => 'public.home'],
        ['title' => 'nav.news', 'route' => 'public.news.index'],
        ['title' => 'nav.team', 'route' => 'public.teams.index'],
        ['title' => 'nav.matches', 'route' => 'public.matches.index'],
        ['title' => 'nav.trainings', 'route' => 'public.trainings.index'],
        ['title' => 'nav.recruitment', 'route' => 'public.recruitment.index'],
        ['title' => 'nav.gallery', 'route' => 'public.galleries.index'],
        ['title' => 'nav.history', 'route' => 'public.history.index'],
        ['title' => 'nav.contact', 'route' => 'public.contact.index'],
    ],

    // Členská sekce: kompletní struktura pro portál
    'member' => [
        'main' => [
            ['title' => 'nav.dashboard', 'route' => 'member.dashboard', 'icon' => 'heroicon-o-home'],
            ['title' => 'nav.my_program', 'route' => 'member.attendance.index', 'icon' => 'heroicon-o-calendar-days'],
            ['title' => 'nav.attendance_history', 'route' => 'member.attendance.history', 'icon' => 'heroicon-o-clock'],
            ['title' => 'nav.my_profile', 'route' => 'member.profile.edit', 'icon' => 'heroicon-o-user'],
            ['title' => 'nav.payments', 'route' => 'member.economy.index', 'icon' => 'heroicon-o-credit-card'],
        ],
        'coach' => [
            ['title' => 'nav.team_overviews', 'route' => 'member.teams.index', 'icon' => 'heroicon-o-user-group'],
        ],
    ],

    // Administrace (vlastní custom stránky mimo Filament)
    'admin' => [
        ['title' => 'nav.admin_dashboard', 'route' => 'admin.dashboard'],
        ['title' => 'nav.content', 'route' => 'admin.content.index'],
        ['title' => 'nav.sports', 'route' => 'admin.sports.index'],
        ['title' => 'nav.attendance', 'route' => 'admin.attendance.index'],
        ['title' => 'nav.users', 'route' => 'admin.users.index'],
        ['title' => 'nav.settings', 'route' => 'admin.settings.index'],
    ],
];
