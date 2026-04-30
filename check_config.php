<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "max_file_size: " . config('media-library.max_file_size') . "\n";
echo "10MB in bytes: " . (10 * 1024 * 1024) . "\n";
echo "100MB in bytes: " . (100 * 1024 * 1024) . "\n";
