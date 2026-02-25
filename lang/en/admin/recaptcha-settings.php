<?php

return [
    'title' => 'reCAPTCHA Settings',
    'navigation' => 'reCAPTCHA',
    'sections' => [
        'general' => 'General reCAPTCHA v3 Settings',
        'general_desc' => 'Set up keys and parameters for form spam protection.',
    ],
    'fields' => [
        'site_key' => 'Site Key',
        'secret_key' => 'Secret Key',
        'threshold' => 'Threshold',
        'threshold_help' => 'Value between 0.0 (likely a bot) and 1.0 (likely a human). Recommended value is 0.5.',
        'enabled' => 'Enable Protection',
    ],
    'notifications' => [
        'saved' => 'reCAPTCHA settings saved successfully.',
        'error' => 'An error occurred while saving settings.',
    ],
    'actions' => [
        'save' => 'Save Settings',
    ],
];
