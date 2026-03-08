<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

config(['database.connections.mysql.prefix' => 'new_']);

$sqls = DB::connection('mysql')->pretend(function() {
    Schema::create('feedback_reports', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('type');
        $table->json('viewport')->nullable();
        $table->timestamps();
    });
});

foreach ($sqls as $sql) {
    echo $sql['query'] . PHP_EOL;
}
