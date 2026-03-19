<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feedback System Configuration
    |--------------------------------------------------------------------------
    */

    'enabled' => env('FEEDBACK_ENABLED', true),

    'environments' => explode(',', env('FEEDBACK_ENVIRONMENTS', 'production,staging,local,testing')),

    'recipients' => env('ERROR_REPORT_EMAIL', env('FEEDBACK_RECIPIENTS', 'it@kbelstisokoli.cz')),

    'notifications' => [
        'mail' => true,
        'queue' => 'default',
    ],

    'limits' => [
        'max_payload_bytes' => 8 * 1024 * 1024, // Zvýšeno na 8MB kvůli screenshotu + DOM dumpu
        'max_console_logs' => 300,
        'max_runtime_errors' => 100,
        'max_network_failures' => 100,
        'max_clicks' => 200,
        'max_breadcrumbs' => 50,
        'duplicate_check_minutes' => 5,
        'rate_limit' => env('FEEDBACK_RATE_LIMIT', '10,1'), // 10 per 1 minute
    ],

    'screenshot' => [
        'strategy' => env('SCREENSHOT_DRIVER', env('FEEDBACK_SCREENSHOT_STRATEGY', 'playwright')), // auto, playwright, html2canvas, none
        'quality' => 0.80,
        'max_width' => 1600,
        'playwright' => [
            'enabled' => env('FEEDBACK_PLAYWRIGHT_ENABLED', true),
            'timeout' => env('SCREENSHOT_TIMEOUT_MS', 30000),
            'node_path' => env('SCREENSHOT_NODE_BINARY', env('FEEDBACK_NODE_PATH', 'node')),
            'chromium_path' => env('SCREENSHOT_CHROMIUM_PATH'),
            'browsers_path' => env('FEEDBACK_PLAYWRIGHT_BROWSERS_PATH'),
            'script_path' => 'resources/js/screenshot-worker.cjs',
            'temp_path' => env('SCREENSHOT_TEMP_DIR', 'storage/app/temp/screenshots'),
            'viewports' => [
                'desktop' => ['width' => 1920, 'height' => 1080],
                'mobile' => ['width' => 390, 'height' => 844],
            ],
            'full_page' => env('FEEDBACK_SCREENSHOT_FULL_PAGE', false),
        ],
    ],

    'screenshot_required' => env('FEEDBACK_SCREENSHOT_REQUIRED', false),

    'dom_snapshot' => [
        'max_length' => 100 * 1024, // 100KB
    ],

    'redaction' => [
        'redact_keys' => [
            'password',
            'password_confirmation',
            'token',
            'authorization',
            'cookie',
            'set-cookie',
            'bearer',
            'api_key',
            'secret',
            'csrf',
            'xsrf-token',
        ],
        'redact_patterns' => [
            '/bearer\s+[a-zA-Z0-9\-\._~\+\/]+=*/i',
        ],
    ],
];
