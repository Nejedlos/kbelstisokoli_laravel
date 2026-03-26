<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Screenshot Mode Configuration
    |--------------------------------------------------------------------------
    */

    'enabled' => env('SCREENSHOT_MODE_ENABLED', true),

    // Interní token pro autorizaci požadavků z NASu (nepovinné pokud se používá Signed URL)
    'internal_token' => env('SCREENSHOT_INTERNAL_TOKEN'),

    // Povolené domény pro Playwright (NAS)
    'allowed_hosts' => explode(',', env('SCREENSHOT_ALLOWED_HOSTS', 'localhost,127.0.0.1')),

    // Seznam cest nebo route names, které lze renderovat přes screenshot endpoint
    'whitelist' => [
        'routes' => [
            // 'public.home',
            // 'admin.dashboard',
        ],
        'paths' => [
            '/*', // Výchozí: povoleno vše, bezpečnost řeší signed URL / token
        ],
    ],

    // Nastavení stability
    'stability' => [
        'delay_ms' => env('SCREENSHOT_STABILITY_DELAY', 500),
        'wait_for_fonts' => true,
    ],
];
