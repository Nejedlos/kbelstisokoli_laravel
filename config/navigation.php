<?php

return [
    // Veřejná navigace (hlavní menu)
    'public' => [
        ['title' => 'Úvod', 'route' => 'public.home'],
        ['title' => 'Novinky', 'route' => 'public.news.index'],
        ['title' => 'Zápasy', 'route' => 'public.matches.index'],
        ['title' => 'Tým', 'route' => 'public.team.index'],
        ['title' => 'Tréninky', 'route' => 'public.trainings.index'],
        ['title' => 'Historie', 'route' => 'public.history.index'],
        ['title' => 'Kontakt', 'route' => 'public.contact.index'],
    ],

    // Členská sekce: kompletní struktura pro portál
    'member' => [
        'main' => [
            ['title' => 'Nástěnka', 'route' => 'member.dashboard', 'icon' => 'heroicon-o-home'],
            ['title' => 'Můj program', 'route' => 'member.attendance.index', 'icon' => 'heroicon-o-calendar-days'],
            ['title' => 'Historie docházky', 'route' => 'member.attendance.history', 'icon' => 'heroicon-o-clock'],
            ['title' => 'Můj profil', 'route' => 'member.profile.edit', 'icon' => 'heroicon-o-user'],
            ['title' => 'Platby a příspěvky', 'route' => 'member.economy.index', 'icon' => 'heroicon-o-credit-card'],
        ],
        'coach' => [
            ['title' => 'Týmové přehledy', 'route' => 'member.teams.index', 'icon' => 'heroicon-o-users-group'],
        ],
    ],

    // Administrace (vlastní custom stránky mimo Filament)
    'admin' => [
        ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
        ['title' => 'Obsah', 'route' => 'admin.content.index'],
        ['title' => 'Sport', 'route' => 'admin.sports.index'],
        ['title' => 'Docházka', 'route' => 'admin.attendance.index'],
        ['title' => 'Uživatelé', 'route' => 'admin.users.index'],
        ['title' => 'Nastavení', 'route' => 'admin.settings.index'],
    ],
];
