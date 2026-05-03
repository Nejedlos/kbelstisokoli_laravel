<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DMARC Monitoring Configuration
    |--------------------------------------------------------------------------
    */

    'alert_to' => env('ERROR_REPORT_EMAIL'),

    'incident_deduplication_hours' => 24,

    'notification_cooldown_hours' => 12,

    'storage_path' => 'dmarc/reports',
];
