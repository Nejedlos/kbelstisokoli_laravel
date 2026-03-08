<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feedback System Configuration
    |--------------------------------------------------------------------------
    */

    'enabled' => env('FEEDBACK_ENABLED', true),

    'environments' => explode(',', env('FEEDBACK_ENVIRONMENTS', 'production,staging,local')),

    'recipients' => explode(',', env('FEEDBACK_RECIPIENTS', 'it@kbelstisokoli.cz')),

    'notifications' => [
        'mail' => true,
        'queue' => 'default',
    ],

    'limits' => [
        'max_payload_bytes' => 6 * 1024 * 1024, // 6MB
        'max_console_logs' => 300,
        'max_runtime_errors' => 100,
        'max_network_failures' => 100,
        'max_clicks' => 200,
        'duplicate_check_minutes' => 5,
        'rate_limit' => '10,1', // 10 per 1 minute
    ],

    'screenshot' => [
        'quality' => 0.80,
        'max_width' => 1920,
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
