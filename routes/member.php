<?php

use App\Http\Controllers\Member\AttendanceController;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\EconomyController;
use App\Http\Controllers\Member\ProfileController;
use App\Http\Controllers\Member\TeamController;
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
        Route::get('/program/{type}/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
        Route::get('/dochazka/historie', [AttendanceController::class, 'history'])->name('attendance.history');
        Route::post('/program/{type}/{id}/respond', [AttendanceController::class, 'store'])->name('attendance.store');

        // Profil
        Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profil', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profil/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
        Route::post('/profil/avatar/select', [ProfileController::class, 'selectAvatarFromAsset'])->name('profile.avatar.select');

        // Ekonomika / Platby (Shell)
        Route::get('/platby', [EconomyController::class, 'index'])->name('economy.index');

        // Notifikace
        Route::get('/notifikace', [\App\Http\Controllers\Member\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifikace/mark-all-read', [\App\Http\Controllers\Member\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
        Route::post('/notifikace/{id}/mark-read', [\App\Http\Controllers\Member\NotificationController::class, 'markAsRead'])->name('notifications.markRead');

        // Vyhledávání (klasické)
        Route::get('/hledat', \App\Http\Controllers\Member\SearchController::class)->name('search');

        // AI vyhledávání
        Route::get('/ai', \App\Http\Controllers\Member\AiController::class)->name('ai');

        // Trenérské přehledy
        Route::get('/tymove-prehledy', [TeamController::class, 'index'])->name('teams.index');
        Route::get('/tymove-prehledy/{team}', [TeamController::class, 'show'])->name('teams.show');

        // Zpětná vazba / Kontakt
        Route::get('/kontakt-trenera', [\App\Http\Controllers\Member\ContactController::class, 'coachForm'])->name('contact.coach.form');
        Route::post('/kontakt-trenera', [\App\Http\Controllers\Member\ContactController::class, 'sendCoach'])->name('contact.coach.send');
        Route::get('/kontakt-admina', [\App\Http\Controllers\Member\ContactController::class, 'adminForm'])->name('contact.admin.form');
        Route::post('/kontakt-admina', [\App\Http\Controllers\Member\ContactController::class, 'sendAdmin'])->name('contact.admin.send');
    });
