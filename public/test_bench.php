<?php
$start = microtime(true);
$marks = [];

$marks['start'] = microtime(true) - $start;

require __DIR__.'/../vendor/autoload.php';
$marks['autoload'] = microtime(true) - $start;

$app = require_once __DIR__.'/../bootstrap/app.php';
$marks['bootstrap_app'] = microtime(true) - $start;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$marks['make_kernel'] = microtime(true) - $start;

// Jen nabootujeme aplikaci bez handlingu requestu
$app->boot();
$marks['app_booted'] = microtime(true) - $start;

header('Content-Type: application/json');
echo json_encode([
    'marks' => array_map(fn($v) => round($v * 1000, 2) . ' ms', $marks),
    'total' => round((microtime(true) - $start) * 1000, 2) . ' ms',
    'opcache' => function_exists('opcache_get_status') ? opcache_get_status(false) : 'N/A'
]);
