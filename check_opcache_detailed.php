<?php
header('Content-Type: application/json');

$opcache_status = function_exists('opcache_get_status') ? opcache_get_status(true) : 'Not available';
$ini_values = [
    'memory_limit' => ini_get('memory_limit'),
    'opcache.enable' => ini_get('opcache.enable'),
    'opcache.memory_consumption' => ini_get('opcache.memory_consumption'),
    'opcache.max_accelerated_files' => ini_get('opcache.max_accelerated_files'),
    'opcache.interned_strings_buffer' => ini_get('opcache.interned_strings_buffer'),
    'opcache.validate_timestamps' => ini_get('opcache.validate_timestamps'),
    'opcache.revalidate_freq' => ini_get('opcache.revalidate_freq'),
];

echo json_encode([
    'php_version' => PHP_VERSION,
    'sapi' => PHP_SAPI,
    'ini' => $ini_values,
    'config_files' => [
        'cfg_file_path' => php_ini_loaded_file(),
        'additional_files' => php_ini_scanned_files(),
        'user_ini_filename' => ini_get('user_ini.filename'),
    ],
    'opcache_status' => $opcache_status,
], JSON_PRETTY_PRINT);
