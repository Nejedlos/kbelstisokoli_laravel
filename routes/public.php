<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| Zde jsou definovány veřejné trasy pro frontend basketbalového oddílu.
| Tyto trasy jsou přístupné komukoliv bez nutnosti přihlášení.
|
*/

Route::name('public.')->group(function (): void {
    Route::get('/uvod', function () {
        return view('public.home');
    })->name('home');
});
