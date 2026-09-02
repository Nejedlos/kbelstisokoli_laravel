<?php

use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\AttendanceEmailController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use App\Http\Controllers\Dev\MailPreviewController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\MediaDownloadController;
use App\Http\Controllers\Public\LanguageController;
use App\Http\Controllers\ScreenshotRenderController;
use App\Http\Controllers\System\CronController;
use App\Http\Middleware\EnsureValidTwoFactorChallenge;
use App\Services\BrandingService;
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
Route::get('/language/{lang}', LanguageController::class)
    ->middleware('web')
    ->name('language.switch');

// Možnost odhlášení přes admin URL (i přes GET pro pohodlí)
Route::match(['get', 'post'], '/admin/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('web')
    ->name('admin.logout');

// Stránka po úspěšném odhlášení
Route::get('/logout-success', function (BrandingService $brandingService) {
    return view('auth.logged-out', [
        'branding' => $brandingService->getSettings(),
        'branding_css' => $brandingService->getCssVariables(),
    ]);
})->name('logout.success');

// Webový trigger pro cron/scheduler
Route::get('/system/cron/run', [CronController::class, 'run'])->name('system.cron.run');

// Společný průvodce: povinný pro přístup do administrace, dobrovolný pro členy.
Route::get('/auth/two-factor-setup', TwoFactorSetupController::class)
    ->middleware(['auth', 'active'])
    ->name('auth.two-factor-setup');

Route::get('/auth/two-factor-complete', [TwoFactorSetupController::class, 'complete'])
    ->middleware(['auth', 'active'])
    ->name('auth.two-factor-complete');

// Zabezpečené stahování médií
Route::get('/media/download/{uuid}', [MediaDownloadController::class, 'download'])
    ->name('media.download');

Route::match(['get', 'post'], '/attendance/email/respond', [AttendanceEmailController::class, 'respond'])
    ->middleware(['signed', 'throttle:30,1'])->name('attendance.email.respond');
Route::match(['get', 'post'], '/attendance/email/unsubscribe', [AttendanceEmailController::class, 'unsubscribe'])
    ->middleware(['signed', 'throttle:10,1'])->name('attendance.email.unsubscribe');

// Feedback systém
Route::get('/feedback/widget', [FeedbackController::class, 'renderWidget'])
    ->middleware(['web', 'auth'])
    ->name('feedback.widget');

Route::post('/feedback', [FeedbackController::class, 'store'])
    ->middleware(['web', 'auth', 'throttle:'.config('feedback.limits.rate_limit', '10,1')])
    ->name('feedback.store');

Route::post('/feedback/screenshot', [FeedbackController::class, 'serverScreenshot'])
    ->middleware(['web', 'auth', 'throttle:5,1'])
    ->name('feedback.screenshot');

Route::get('/feedback/snapshot/{token}', [FeedbackController::class, 'snapshot'])
    ->name('feedback.snapshot');

// Impersonifikace uživatelů (pro adminy)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/users/search-ajax', [ImpersonateController::class, 'search'])
        ->name('admin.impersonate.search');
    Route::get('/admin/impersonate/{userId}', [ImpersonateController::class, 'start'])
        ->name('admin.impersonate.start');
    Route::get('/admin/impersonate-stop', [ImpersonateController::class, 'stop'])
        ->name('admin.impersonate.stop');

    // Privátní data z feedbacku (pouze pro adminy)
    Route::get('/admin/feedback-reports/{report}/screenshot', [FeedbackController::class, 'screenshot'])
        ->name('admin.feedback.screenshot');
});

// --- Override Fortify two-factor challenge routes to allow authenticated users ---
// Důvod: standardní Fortify route používá 'guest' (RedirectIfAuthenticated),
// což u našeho flow (uživatel je přihlášen a vyžádána 2FA) způsobí redirect na HOME.
// Přeregistrujeme GET/POST se stejnou cestou a jménem, ale bez 'guest' middleware.
Route::get('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
    ->middleware(['web', EnsureValidTwoFactorChallenge::class])
    ->name('two-factor.login');

Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
    ->middleware(['web', 'throttle:two-factor', EnsureValidTwoFactorChallenge::class])
    ->name('two-factor.login.store');

// --- Mail Preview (pouze pro local nebo adminy s právy) ---
Route::middleware(['web'])->group(function () {
    Route::get('/dev/mail-preview', [MailPreviewController::class, 'index'])
        ->name('dev.mail-preview.index');
    Route::get('/dev/mail-preview/{type}', [MailPreviewController::class, 'show'])
        ->name('dev.mail-preview.show');
});

// --- Screenshot System ---
Route::middleware(['web'])->group(function () {
    // Globální endpoint pro renderování screenshotů (zabezpečený interním tokenem nebo auth)
    Route::get('/system/screenshot/render', [ScreenshotRenderController::class, 'render'])
        ->name('screenshot.render');

    // Virtuální route pro ověření podepsaných URL v middleware
    Route::get('/system/screenshot/proxy/{target_path}', function () {
        abort(404); // Tato trasa se nikdy reálně nevykoná, slouží jen pro generování/ověření signatury
    })->name('screenshot.proxy')->where('target_path', '.*');
});

// --- Redirects ze starého webu ---
require __DIR__.'/redirects.php';
