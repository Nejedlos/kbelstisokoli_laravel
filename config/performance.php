<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Scenarios
    |--------------------------------------------------------------------------
    |
    | 'standard'   - Basic optimizations (Eager loading, DB indexes)
    | 'aggressive' - Adds Fragment caching and HTML minification
    | 'ultra'      - Adds Full Page caching and Livewire navigation (SPA feel)
    |
    */
    'scenario' => env('PERF_SCENARIO', 'ultra'),

    'features' => [
        'full_page_cache' => env('PERF_FULL_PAGE_CACHE', true),
        'fragment_cache' => env('PERF_FRAGMENT_CACHE', true),
        'html_minification' => env('PERF_HTML_MINIFY', true),
        'livewire_navigate' => env('PERF_LW_NAVIGATE', true),
        'lazy_load_images' => env('PERF_LAZY_IMAGES', true),
    ],

    'cache_ttl' => [
        'fragments' => 3600,
        'full_page' => 86400,
    ],
];
