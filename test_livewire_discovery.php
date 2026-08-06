<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Livewire\Livewire;

try {
    $registry = app(\Livewire\Mechanisms\HandleComponents\ComponentRegistry::class);
    $class = $registry->get('public.hero-events');
    echo "Component 'public.hero-events' resolved to: " . $class . PHP_EOL;
} catch (\Exception $e) {
    echo "Error resolving 'public.hero-events': " . $e->getMessage() . PHP_EOL;
}

try {
    $registry = app(\Livewire\Mechanisms\HandleComponents\ComponentRegistry::class);
    $class = $registry->get('public.standings-table');
    echo "Component 'public.standings-table' resolved to: " . $class . PHP_EOL;
} catch (\Exception $e) {
    echo "Error resolving 'public.standings-table': " . $e->getMessage() . PHP_EOL;
}
