<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SportsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (Custom)
|--------------------------------------------------------------------------
|
| Zde jsou definovány vlastní trasy pro administraci, které nejsou
| automaticky spravovány Filamentem.
|
| Prefix: /admin/custom
|
*/

Route::middleware(['admin'])
    ->prefix('admin/custom')
    ->name('admin.')
    ->group(function (): void {
        // Dashboard (vlastní mimo Filament)
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        // Obsah
        Route::get('/content', [ContentController::class, 'index'])->name('content.index');

        // Sportovní agenda
        Route::get('/sports', [SportsController::class, 'index'])->name('sports.index');

        // Docházka
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

        // Uživatelé
        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        // Nastavení
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    });
