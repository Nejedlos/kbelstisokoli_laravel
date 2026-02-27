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

Route::name('public.')->middleware(['public.maintenance', 'redirects'])->group(function (): void {
    // Úvod
    Route::get('/', HomeController::class)->name('home');

    // Novinky
    Route::get('/novinky', [NewsController::class, 'index'])->name('news.index');
    Route::get('/novinky/{slug}', [NewsController::class, 'show'])->name('news.show');

    // Zápasy
    Route::get('/zapasy', [MatchController::class, 'index'])->name('matches.index');
    Route::get('/zapasy/{id}', [MatchController::class, 'show'])->name('matches.show');

    // Týmy (plural hlavní přehled)
    Route::get('/tymy', [TeamController::class, 'index'])->name('teams.index');
    Route::get('/tymy/{slug}', [TeamController::class, 'show'])->name('teams.show');

    // Zpětná kompatibilita: /tym -> 301 redirect na /tymy
    Route::get('/tym', function () {
        return redirect('/tymy', 301);
    });
    Route::get('/tym/{slug}', function ($slug) {
        return redirect('/tymy/' . $slug, 301);
    });

    // Galerie
    Route::get('/galerie', [\App\Http\Controllers\Public\GalleryController::class, 'index'])->name('galleries.index');
    Route::get('/galerie/{slug}', [\App\Http\Controllers\Public\GalleryController::class, 'show'])->name('galleries.show');

    // Tréninky
    Route::get('/treninky', [TrainingController::class, 'index'])->name('trainings.index');

    // Historie
    Route::get('/historie', [HistoryController::class, 'index'])->name('history.index');

    // Kontakt
    Route::get('/kontakt', [ContactController::class, 'index'])->name('contact.index');
    Route::post('/kontakt', [\App\Http\Controllers\PublicLeadController::class, 'storeContact'])->name('contact.store')->middleware('throttle:5,1');
    Route::get('/napiste-nam', function (\Illuminate\Http\Request $request) {
        return view('public.contact.form', ['to' => $request->query('to', '')]);
    })->name('contact-form');

    // Nábor – GET (statická landing page)
    Route::get('/nabor', function () {
        $page = \App\Models\Page::where('slug', 'nabor')->first();
        $teams = \App\Models\Team::where('category', 'senior')->orderBy('slug')->get();
        return view('public.recruitment', compact('page', 'teams'));
    })->name('recruitment.index');

    // Nábor – Samostatná stránka s formulářem
    Route::get('/join/{team?}', function ($team = null) {
        $homePage = \App\Models\Page::where('slug', 'home')->first();
        $seo = app(\App\Services\SeoService::class)->getMetadata($homePage); // Základní SEO z homepage
        $seo['title'] = 'Chci hrát za C & E | Kbelští sokoli';
        return view('public.join', compact('team', 'seo'));
    })->name('recruitment.join');

    // Nábor – POST (zpracování leadu)
    Route::post('/nabor', [\App\Http\Controllers\PublicLeadController::class, 'storeRecruitment'])->name('recruitment.store')->middleware('throttle:5,1');

    // Vyhledávání
    Route::get('/hledat', [\App\Http\Controllers\Public\SearchController::class, 'index'])->name('search');

    // Sitemap & Robots
    Route::get('/sitemap.xml', [\App\Http\Controllers\Public\SitemapController::class, 'index'])->name('sitemap');
    Route::get('/robots.txt', [\App\Http\Controllers\Public\SitemapController::class, 'robots'])->name('robots');

    // Generické stránky (vždy na konci skupiny)
    Route::get('/{slug}', [PageController::class, 'show'])
        ->name('pages.show')
        ->where('slug', '^(?!admin|clenska-sekce|login|logout|two-factor|auth|user|api|up|system).*$');
});
