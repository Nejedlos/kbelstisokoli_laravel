<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DMARC Monitoring Configuration
    |--------------------------------------------------------------------------
    */

    'alert_to' => env('DMARC_ALERT_EMAIL', env('ERROR_REPORT_EMAIL', env('TECHNICAL_CONTACT_EMAIL'))),

    'alerts' => [
        'enabled' => env('DMARC_ALERTS_ENABLED', true),
        'technical_contact_email' => env('DMARC_ALERT_EMAIL', env('ERROR_REPORT_EMAIL', env('TECHNICAL_CONTACT_EMAIL'))),
        'critical_only' => env('DMARC_ALERTS_CRITICAL_ONLY', true),
        'min_severity' => env('DMARC_ALERTS_MIN_SEVERITY', 'critical'),
        'rate_limit_hours' => env('DMARC_ALERTS_RATE_LIMIT_HOURS', 12),
        'min_count_for_critical' => env('DMARC_ALERTS_MIN_COUNT_FOR_CRITICAL', 1),
    ],

    'ip_enrichment' => [
        'enabled' => env('DMARC_IP_ENRICHMENT_ENABLED', true),
        'cache_hours' => env('DMARC_IP_ENRICHMENT_CACHE_HOURS', 168),
    ],

    'dns_check' => [
        'enabled' => env('DMARC_DNS_CHECK_ENABLED', true),
        'cache_hours' => env('DMARC_DNS_CHECK_CACHE_HOURS', 24),
    ],

    'incident_deduplication_hours' => 24,

    'notification_cooldown_hours' => 12,

    'storage_path' => 'dmarc/reports',
];
