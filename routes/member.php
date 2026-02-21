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

        // Docházka
        Route::get('/dochazka', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/dochazka/{id}/potvrdit', [AttendanceController::class, 'confirm'])->name('attendance.confirm');
        Route::post('/dochazka/{id}/odmitnout', [AttendanceController::class, 'decline'])->name('attendance.decline');

        // Profil
        Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profil', [ProfileController::class, 'update'])->name('profile.update');
    });
