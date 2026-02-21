<?php

use App\Http\Controllers\Member\AttendanceController;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\ProfileController;
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
        // Dashboard
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        // Docházka / Program
        Route::get('/program', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/program/{type}/{id}/respond', [AttendanceController::class, 'store'])->name('attendance.store');

        // Profil
        Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profil', [ProfileController::class, 'update'])->name('profile.update');
    });
