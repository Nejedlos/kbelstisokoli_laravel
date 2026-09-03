<?php

namespace App\Http\Middleware;

use App\Support\ScreenshotMode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class DetectScreenshotMode
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        ScreenshotMode::deactivate();

        try {
            if (config('screenshot.enabled', true) && ScreenshotMode::shouldActivate($request)) {
                $userId = $request->query('screenshot_user_id');
                ScreenshotMode::activate($userId);

                Log::debug('[ScreenshotMode] Detected in request', [
                    'user_id' => $userId,
                    'url' => $request->fullUrl(),
                    'headers' => [
                        'X-Screenshot-Token' => $request->hasHeader('X-Screenshot-Token') ? 'PRESENT' : 'MISSING',
                        'X-Screenshot-Mode' => $request->header('X-Screenshot-Mode'),
                    ],
                ]);

                // Zajištění absolutních URL pro assety
                URL::forceRootUrl(config('app.url'));

                // Autorizace impersonifikace:
                if ($userId) {
                    $internalToken = config('screenshot.internal_token');
                    $isAuthenticated = ($internalToken && $request->header('X-Screenshot-Token') === $internalToken)
                                       || $request->hasValidSignature();

                    if ($isAuthenticated) {
                        try {
                            // Přihlášení uživatele POUZE PRO TENTO JEDEN REQUEST (jednorázová impersonifikace)
                            // Nepoužíváme loginUsingId, protože by se to trvale uložilo do session uživatele.
                            $guard = $request->is('admin*') ? 'web' : config('auth.defaults.guard', 'web');
                            Auth::guard($guard)->onceUsingId($userId);

                            // Výjimka platí jen pro tento autorizovaný požadavek, nikdy pro další session.
                            $request->attributes->set('two_factor_trusted_screenshot', true);

                            Log::info('[ScreenshotMode] User impersonated (once)', [
                                'user_id' => $userId,
                                'auth_id' => Auth::id(),
                            ]);
                        } catch (\Throwable $e) {
                            Log::error('[ScreenshotMode] Impersonation failed', [
                                'error' => $e->getMessage(),
                            ]);
                        }
                    } else {
                        Log::warning('[ScreenshotMode] User ID provided but not authenticated', [
                            'user_id' => $userId,
                        ]);
                    }
                }
            }

            return $next($request);
        } finally {
            ScreenshotMode::deactivate();
        }
    }
}
