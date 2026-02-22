<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Zde jsou definovány základní systémové trasy.
| Většina veřejného obsahu je v routes/public.php.
|
*/

// Logout trasa musí být přístupná i bez auth middleware pro zrušení 2FA challenge
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('web')
    ->name('logout');

// Možnost odhlášení přes admin URL (i přes GET pro pohodlí)
Route::match(['get', 'post'], '/admin/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('web')
    ->name('admin.logout');

// Webový trigger pro cron/scheduler
Route::get('/system/cron/run', [\App\Http\Controllers\System\CronController::class, 'run'])->name('system.cron.run');

// Povinný 2FA setup pro adminy
Route::get('/auth/two-factor-setup', \App\Http\Controllers\Auth\TwoFactorSetupController::class)
    ->middleware(['auth', 'active'])
    ->name('auth.two-factor-setup');
