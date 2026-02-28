<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;
use App\Http\Controllers\MediaDownloadController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Zde jsou definovány základní systémové trasy.
| Většina veřejného obsahu je v routes/public.php.
|
*/

// Logout trasa musí být přístupná i bez auth middleware pro zrušení 2FA challenge
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('web')
    ->name('logout');

// Sjednocení přihlašovací stránky na admin/login (Filament)
Route::get('/login', fn () => redirect()->to('/admin/login'))->name('login');

// Změna jazyka (moderní přístup přes session)
Route::get('/language/{lang}', \App\Http\Controllers\Public\LanguageController::class)
    ->middleware('web')
    ->name('language.switch');

// Možnost odhlášení přes admin URL (i přes GET pro pohodlí)
Route::match(['get', 'post'], '/admin/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('web')
    ->name('admin.logout');

// Webový trigger pro cron/scheduler
Route::get('/system/cron/run', [\App\Http\Controllers\System\CronController::class, 'run'])->name('system.cron.run');

// Povinný 2FA setup pro adminy
Route::get('/auth/two-factor-setup', \App\Http\Controllers\Auth\TwoFactorSetupController::class)
    ->middleware(['auth', 'active'])
    ->name('auth.two-factor-setup');

// Zabezpečené stahování médií
Route::get('/media/download/{uuid}', [MediaDownloadController::class, 'download'])
    ->name('media.download');

// Impersonifikace uživatelů (pro adminy)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/users/search-ajax', [\App\Http\Controllers\Admin\ImpersonateController::class, 'search'])
        ->name('admin.impersonate.search');
    Route::get('/admin/impersonate/{userId}', [\App\Http\Controllers\Admin\ImpersonateController::class, 'start'])
        ->name('admin.impersonate.start');
    Route::get('/admin/impersonate-stop', [\App\Http\Controllers\Admin\ImpersonateController::class, 'stop'])
        ->name('admin.impersonate.stop');
});



// --- Override Fortify two-factor challenge routes to allow authenticated users ---
// Důvod: standardní Fortify route používá 'guest' (RedirectIfAuthenticated),
// což u našeho flow (uživatel je přihlášen a vyžádána 2FA) způsobí redirect na HOME.
// Přeregistrujeme GET/POST se stejnou cestou a jménem, ale bez 'guest' middleware.
Route::get('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
    ->middleware(['web'])
    ->name('two-factor.login');

Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
    ->middleware(['web', 'throttle:two-factor'])
    ->name('two-factor.login.store');
