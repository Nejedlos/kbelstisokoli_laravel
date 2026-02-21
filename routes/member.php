<?php

use App\Http\Controllers\Member\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Member Routes
|--------------------------------------------------------------------------
|
| Zde jsou definovány trasy pro členskou sekci (pro hráče a trenéry).
| Tyto trasy jsou chráněny autentizací.
|
| Prefix: /clenska-sekce
|
*/

Route::middleware(['member'])
    ->prefix('clenska-sekce')
    ->name('member.')
    ->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
    });
