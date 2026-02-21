<?php

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
    ->name('admin.custom.')
    ->group(function (): void {
        Route::get('/dashboard-extra', function () {
            return "Extra Admin Dashboard - Placeholder";
        })->name('dashboard');
    });
