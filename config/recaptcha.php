<?php

return [
    'enabled' => env('RECAPTCHA_ENABLED', true),
    'site_key' => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    'score_threshold' => (float) env('RECAPTCHA_SCORE_THRESHOLD', 0.5),
    'timeout' => (int) env('RECAPTCHA_TIMEOUT', 3),
];
