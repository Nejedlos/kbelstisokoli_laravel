<?php

use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HistoryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\MatchController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\TeamController;
use App\Http\Controllers\Public\TrainingController;
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

Route::name('public.')->middleware(['public.maintenance'])->group(function (): void {
    // Úvod
    Route::get('/', HomeController::class)->name('home');

    // Novinky
    Route::get('/novinky', [NewsController::class, 'index'])->name('news.index');
    Route::get('/novinky/{slug}', [NewsController::class, 'show'])->name('news.show');

    // Zápasy
    Route::get('/zapasy', [MatchController::class, 'index'])->name('matches.index');
    Route::get('/zapasy/{id}', [MatchController::class, 'show'])->name('matches.show');

    // Tým
    Route::get('/tym', [TeamController::class, 'index'])->name('team.index');

    // Galerie
    Route::get('/galerie', [\App\Http\Controllers\Public\GalleryController::class, 'index'])->name('galleries.index');
    Route::get('/galerie/{slug}', [\App\Http\Controllers\Public\GalleryController::class, 'show'])->name('galleries.show');

    // Tréninky
    Route::get('/treninky', [TrainingController::class, 'index'])->name('trainings.index');

    // Historie
    Route::get('/historie', [HistoryController::class, 'index'])->name('history.index');

    // Kontakt
    Route::get('/kontakt', [ContactController::class, 'index'])->name('contact.index');

    // Sitemap & Robots
    Route::get('/sitemap.xml', [\App\Http\Controllers\Public\SitemapController::class, 'index'])->name('sitemap');
    Route::get('/robots.txt', [\App\Http\Controllers\Public\SitemapController::class, 'robots'])->name('robots');

    // Generické stránky (vždy na konci skupiny)
    Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show');
});
