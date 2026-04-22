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

// Cron / Plánovač (HTTP spuštění)
Route::get('/system/schedule/{token}', function (string $token) {
    // Pro delší úlohy (např. synchronizace statistik) zvýšíme časový limit na 5 minut
    set_time_limit(300);

    if (empty(config('app.schedule_token')) || $token !== config('app.schedule_token')) {
        abort(403, 'Neplatný token.');
    }

    \Illuminate\Support\Facades\Artisan::call('schedule:run');
    $output = \Illuminate\Support\Facades\Artisan::output();

    // Heartbeat logování pro diagnostiku
    $isHeartbeat = str_contains($output, 'Running scheduled command: (callable)')
        || str_contains($output, 'Running [Callback]')
        || str_contains($output, 'No scheduled commands are ready to run');

    $cacheDriver = config('cache.default');
    $storagePath = storage_path();
    $cachePath = config('cache.stores.file.path');
    $isWritable = is_writable($cachePath);

    \Illuminate\Support\Facades\Log::info('Schedule:run endpoint hit', [
        'ip' => request()->ip(),
        'ua' => request()->userAgent(),
        'is_heartbeat' => $isHeartbeat,
        'output_len' => strlen($output),
        'cache_driver' => $cacheDriver,
        'storage_path' => $storagePath,
        'cache_path' => $cachePath,
        'is_writable' => $isWritable,
    ]);

    if ($isHeartbeat) {
        \Illuminate\Support\Facades\Log::info('Schedule:run triggered heartbeat from HTTP (or no commands ready).');
    } else {
        \Illuminate\Support\Facades\Log::warning('Schedule:run DID NOT trigger heartbeat from HTTP matching known strings. Output: ' . $output);
    }

    $writeSuccess = false;
    $writeTime = null;
    $errorMessage = null;

    // Vždy se pokusíme o zápis heartbeatu přímo zde, abychom měli jistotu
    try {
        $now = now();
        \Illuminate\Support\Facades\Cache::put('scheduler_heartbeat', $now);
        \Illuminate\Support\Facades\Cache::store('file')->put('scheduler_heartbeat', $now); // Explicitní zápis i do file storu

        $verify = \Illuminate\Support\Facades\Cache::get('scheduler_heartbeat');
        $writeSuccess = ($verify !== null);
        $writeTime = $verify ? (is_string($verify) ? $verify : $verify->toDateTimeString()) : null;

        \Illuminate\Support\Facades\Log::info('Schedule:run explicit heartbeat write check', [
            'success' => $writeSuccess,
            'time' => $writeTime,
        ]);
    } catch (\Exception $e) {
        $errorMessage = $e->getMessage();
        \Illuminate\Support\Facades\Log::error('Schedule:run heartbeat write FAILED: ' . $errorMessage);
    }

    if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest' || request()->has('json')) {
        return response()->json([
            'status' => 'success',
            'message' => 'Plánované úlohy byly spuštěny.',
            'heartbeat' => [
                'success' => $writeSuccess,
                'time' => $writeTime,
                'error' => $errorMessage,
                'cache_driver' => $cacheDriver,
                'storage_path' => $storagePath,
                'cache_path' => $cachePath,
                'is_writable' => $isWritable,
            ],
            'output' => $output,
        ]);
    }

    return response('Plánované úlohy byly spuštěny.'.PHP_EOL.$output);
});

Route::name('public.')->middleware(['public.maintenance', 'redirects'])->group(function (): void {
    // Úvod
    Route::get('/', HomeController::class)->name('home');

    // O klubu
    Route::get('/o-klubu', function () {
        return view('public.about');
    })->name('about');

    // Novinky
    Route::get('/novinky', [NewsController::class, 'index'])->name('news.index');
    Route::get('/novinky/{slug}', [NewsController::class, 'show'])->name('news.show');

    // Zápasy
    Route::get('/zapasy', [MatchController::class, 'index'])->name('matches.index');
    Route::get('/zapasy/{id}', [MatchController::class, 'show'])->name('matches.show');

    // Týmy (plural hlavní přehled)
    Route::get('/tymy', [TeamController::class, 'index'])->name('teams.index');
    Route::get('/tymy/soupisky', [TeamController::class, 'roster'])->name('teams.roster');
    Route::get('/tymy/{slug}', [TeamController::class, 'show'])->name('teams.show');

    // Zpětná kompatibilita: /tym -> 301 redirect na /tymy
    Route::get('/tym', function () {
        return redirect('/tymy', 301);
    });
    Route::get('/tym/{slug}', function ($slug) {
        return redirect('/tymy/'.$slug, 301);
    });

    // Galerie
    Route::get('/galerie', [\App\Http\Controllers\Public\GalleryController::class, 'index'])->name('galleries.index');
    Route::get('/galerie/{slug}', [\App\Http\Controllers\Public\GalleryController::class, 'show'])->name('galleries.show');

    // Tréninky
    Route::get('/treninky', [TrainingController::class, 'index'])->name('trainings.index');

    // Akce (Turnaje, Soustředění)
    Route::get('/akce', [\App\Http\Controllers\Public\ClubEventController::class, 'index'])->name('events.index');
    Route::get('/akce/{id}', [\App\Http\Controllers\Public\ClubEventController::class, 'show'])->name('events.show');

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
        $teams = \App\Models\Team::where('category', 'senior')->orderBy('slug')->get();

        return view('public.recruitment', compact('teams'));
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

    // GDPR
    Route::get('/gdpr', function () {
        return view('public.gdpr');
    })->name('gdpr');

    // Robots.txt & Sitemap & LLMs
    Route::get('/robots.txt', function () {
        return response(file_get_contents(public_path('robots.txt')), 200, ['Content-Type' => 'text/plain']);
    });
    Route::get('/llms.txt', function () {
        return response(file_get_contents(public_path('llms.txt')), 200, ['Content-Type' => 'text/plain']);
    });
    Route::get('/sitemap.xml', function () {
        if (file_exists(public_path('sitemap.xml'))) {
            return response()->file(public_path('sitemap.xml'), ['Content-Type' => 'text/xml']);
        }

        return response()->view('public.sitemap', [
            'pages' => \App\Models\Page::where('status', 'published')->where('is_visible', true)->get(),
            'posts' => \App\Models\Post::where('status', 'published')->where('is_visible', true)->get(),
            'galleries' => \App\Models\Gallery::where('is_public', true)->where('is_visible', true)->get(),
        ], 200)->header('Content-Type', 'text/xml');
    })->name('sitemap');

    // Generic pages (always at the end of the group)
    Route::get('/{slug}', [PageController::class, 'show'])
        ->name('pages.show')
        ->where('slug', '^(?!admin|clenska-sekce|login|logout|logout-success|two-factor|auth|user|api|up|system|robots\.txt|sitemap\.xml|llms\.txt).*$');
});
