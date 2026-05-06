<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Internal Analytics Enabled
    |--------------------------------------------------------------------------
    |
    | Zde můžete globálně zapnout nebo vypnout interní analytiku.
    |
    */
    'enabled' => env('INTERNAL_ANALYTICS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | Počet dní, po které se uchovávají surová data (events).
    | Agregovaná data (summaries) se uchovávají trvale.
    |
    */
    'retention_days' => env('INTERNAL_ANALYTICS_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Privacy & Anonymization
    |--------------------------------------------------------------------------
    */
    'anonymize_ip' => true,
    'hash_salt' => env('INTERNAL_ANALYTICS_SALT', env('APP_KEY')),

    /*
    |--------------------------------------------------------------------------
    | Tracking Options
    |--------------------------------------------------------------------------
    */
    'track_guests' => true,
    'track_authenticated' => true,

    'track_frontend' => true,
    'track_member' => true,
    'track_admin' => true,
    'track_api' => true,

    /*
    |--------------------------------------------------------------------------
    | Filters & Exclusions
    |--------------------------------------------------------------------------
    */
    'ignored_paths' => [
        'admin/assets/*',
        'filament/assets/*',
        'livewire/livewire.js',
        'livewire/livewire.js.map',
        'favicon.ico',
        'apple-touch-icon*',
        'android-chrome*',
        'safari-pinned-tab.svg',
        'browserconfig.xml',
        'robots.txt',
        'sitemap.xml',
        'llms.txt',
        'storage/*',
        'assets/*',
        'build/*',
        'telescope*',
        'horizon*',
        'vendor/*',
        '__clockwork/*',
        '_debugbar/*',
    ],

    'ignored_route_names' => [
        'debugbar.*',
        'telescope*',
        'horizon*',
        'ignition.*',
    ],

    'ignored_methods' => [
        'HEAD',
        'OPTIONS',
    ],

    'ignored_user_agents' => [
        'UptimeRobot',
        'Pingdom',
        'Googlebot',
        'Bingbot',
        'Baiduspider',
        'YandexBot',
        'Sogou',
        'Exabot',
        'facebot',
        'facebookexternalhit',
        'ia_archiver',
    ],

    'ignored_extensions' => [
        'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico', 'css', 'js', 'map', 'woff', 'woff2', 'ttf', 'eot', 'mp4', 'pdf',
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance & Thresholds
    |--------------------------------------------------------------------------
    */
    'slow_request_threshold_ms' => env('INTERNAL_ANALYTICS_SLOW_THRESHOLD', 1000),
    'sample_rate' => env('INTERNAL_ANALYTICS_SAMPLE_RATE', 1.0),
    'aggregate_enabled' => env('INTERNAL_ANALYTICS_AGGREGATE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Special Filters
    |--------------------------------------------------------------------------
    */
    'bot_detection_enabled' => true,
    'livewire_noise_filter_enabled' => true, // Ignorovat Livewire update requesty jako samostatné pageviews
];
