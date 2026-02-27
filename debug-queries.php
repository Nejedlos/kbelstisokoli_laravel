<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

DB::enableQueryLog();

$request = Illuminate\Http\Request::create('/', 'GET');
$response = $kernel->handle($request);

$queries = DB::getQueryLog();

echo "Total queries: " . count($queries) . "\n";

$counts = [];
foreach ($queries as $query) {
    $sql = $query['query'];
    // Nahradíme parametry pro lepší agregaci
    $sql = preg_replace('/(\'|").*?(\'|")/', '?', $sql);
    $sql = preg_replace('/\d+/', '?', $sql);

    if (!isset($counts[$sql])) {
        $counts[$sql] = 0;
    }
    $counts[$sql]++;
}

arsort($counts);

echo "\nTop duplicated queries:\n";
foreach (array_slice($counts, 0, 20) as $sql => $count) {
    if ($count > 1) {
        echo "[$count] $sql\n";
    }
}
