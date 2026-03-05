<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BasketballMatch;
use Illuminate\Support\Facades\DB;

DB::listen(function($query) {
    echo "SQL: " . $query->sql . "\n";
    echo "Bindings: " . json_encode($query->bindings) . "\n";
});

echo "Test 1: LIKE query\n";
BasketballMatch::where('season_id', 3)
    ->where('team_id', 1)
    ->where('metadata', 'LIKE', '%"external_id":"518460"%')
    ->first();

echo "\nTest 2: JSON query (the one that SHOULD NOT BE HERE)\n";
BasketballMatch::where('season_id', 3)
    ->where('team_id', 1)
    ->where('metadata->external_id', 518460)
    ->first();
