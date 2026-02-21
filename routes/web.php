<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Zde jsou definovány základní systémové trasy.
| Většina veřejného obsahu je v routes/public.php.
|
*/

// Webový trigger pro cron/scheduler
Route::get('/system/cron/run', [\App\Http\Controllers\System\CronController::class, 'run'])->name('system.cron.run');
