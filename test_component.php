<?php

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

try {
    echo "Rendering sync-status-bar...\n";
    $output = Blade::render('<livewire:sync-status-bar />');
    echo "Success! Output length: " . strlen($output) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
