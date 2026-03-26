<?php

use App\Http\Controllers\MediaDownloadController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;

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

// Stránka po úspěšném odhlášení
Route::get('/logout-success', function (\App\Services\BrandingService $brandingService) {
    return view('auth.logged-out', [
        'branding' => $brandingService->getSettings(),
        'branding_css' => $brandingService->getCssVariables(),
    ]);
})->name('logout.success');

// Webový trigger pro cron/scheduler
Route::get('/system/cron/run', [\App\Http\Controllers\System\CronController::class, 'run'])->name('system.cron.run');

// Povinný 2FA setup pro adminy
Route::get('/auth/two-factor-setup', \App\Http\Controllers\Auth\TwoFactorSetupController::class)
    ->middleware(['auth', 'active'])
    ->name('auth.two-factor-setup');

// Zabezpečené stahování médií
Route::get('/media/download/{uuid}', [MediaDownloadController::class, 'download'])
    ->name('media.download');

// Feedback systém
Route::get('/feedback/widget', [\App\Http\Controllers\FeedbackController::class, 'renderWidget'])
    ->middleware(['web', 'auth'])
    ->name('feedback.widget');

Route::post('/feedback', [\App\Http\Controllers\FeedbackController::class, 'store'])
    ->middleware(['web', 'auth', 'throttle:' . config('feedback.limits.rate_limit', '10,1')])
    ->name('feedback.store');

Route::post('/feedback/screenshot', [\App\Http\Controllers\FeedbackController::class, 'serverScreenshot'])
    ->middleware(['web', 'auth', 'throttle:5,1'])
    ->name('feedback.screenshot');

Route::get('/feedback/snapshot/{token}', [\App\Http\Controllers\FeedbackController::class, 'snapshot'])
    ->name('feedback.snapshot');

// Impersonifikace uživatelů (pro adminy)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/users/search-ajax', [\App\Http\Controllers\Admin\ImpersonateController::class, 'search'])
        ->name('admin.impersonate.search');
    Route::get('/admin/impersonate/{userId}', [\App\Http\Controllers\Admin\ImpersonateController::class, 'start'])
        ->name('admin.impersonate.start');
    Route::get('/admin/impersonate-stop', [\App\Http\Controllers\Admin\ImpersonateController::class, 'stop'])
        ->name('admin.impersonate.stop');

    // Privátní data z feedbacku (pouze pro adminy)
    Route::get('/admin/feedback-reports/{report}/screenshot', [\App\Http\Controllers\FeedbackController::class, 'screenshot'])
        ->name('admin.feedback.screenshot');
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

// --- Mail Preview (pouze pro local nebo adminy s právy) ---
Route::middleware(['web'])->group(function () {
    Route::get('/dev/mail-preview', [\App\Http\Controllers\Dev\MailPreviewController::class, 'index'])
        ->name('dev.mail-preview.index');
});

