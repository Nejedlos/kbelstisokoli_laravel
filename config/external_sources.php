<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Globální vypínač externích zdrojů
    |--------------------------------------------------------------------------
    */
    'enabled' => env('EXTERNAL_STATS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Nastavení pro cz.basketball
    |--------------------------------------------------------------------------
    */
    'czbasketball' => [
        'enabled' => true,

        // Výchozí limity pro jeden běh synchronizace
        'limits' => [
            'max_match_details_per_run' => 100,
            'recent_match_days' => 3, // kolik dní zpětně kontrolovat boxscore
        ],

        // Nastavení scheduleru
        'schedule' => [
            'baseline_time' => '03:30',
            'match_day_frequency_minutes' => 120, // Každé 2 hodiny
            'match_day_window' => ['10:00', '23:00'],
        ],

        // Seznam týmů k synchronizaci (slugy)
        'teams' => ['muzi-c', 'muzi-e'],
    ],
];
