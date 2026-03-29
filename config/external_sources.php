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

        // Nastavení pro stahování (fetcher)
        'fetcher' => [
            'timeout' => env('CZBASKETBALL_FETCH_TIMEOUT', 45), // Zvýšeno z 15 na 45
            'retry_count' => env('CZBASKETBALL_FETCH_RETRY_COUNT', 3), // Zvýšeno z 2 na 3
            'retry_delay' => env('CZBASKETBALL_FETCH_RETRY_DELAY', 2000), // Zvýšeno z 1000 na 2000 ms
        ],

        // Výchozí limity pro jeden běh synchronizace
        'limits' => [
            'max_match_details_per_run' => 100,
            'recent_match_days' => 7, // kolik dní zpětně kontrolovat boxscore
        ],

        // Nastavení scheduleru
        'schedule' => [
            'baseline_time' => '03:30',
            'match_day_frequency_minutes' => 60, // Každou hodinu
            'match_day_window' => ['00:00', '23:59'],
        ],

        // Seznam týmů k synchronizaci (slugy)
        'teams' => ['muzi-c', 'muzi-e'],
    ],
];
