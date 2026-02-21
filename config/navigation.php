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

    // Členská sekce: rozděleno na header (rychlé odkazy) a sidebar (sekce)
    'member' => [
        'header' => [
            ['title' => 'Dashboard', 'route' => 'member.dashboard'],
            ['title' => 'Profil', 'route' => 'member.profile.edit'],
        ],
        'sidebar' => [
            ['title' => 'Docházka', 'route' => 'member.attendance.index'],
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
