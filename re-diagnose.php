<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

config(['database.connections.mysql.database' => 'kbelstisokoli']);
config(['database.connections.mysql.prefix' => 'new_']);
DB::purge('mysql');

echo "DB Database: " . config('database.connections.mysql.database') . "\n";
echo "DB Prefix: " . config('database.connections.mysql.prefix') . "\n";

$users = DB::table('users')->get();
echo "Count via DB::table('users'): " . $users->count() . "\n";

foreach ($users as $u) {
    echo " - ID: {$u->id}, Email: {$u->email}\n";
}
