<?php

namespace App\Http\Middleware;

use App\Models\NotFoundLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotFoundLoggerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Zkontrolujeme, zda jde o 404 response
        if ($response instanceof Response && $response->getStatusCode() === 404) {
            $this->logNotFound($request);
        }

        return $response;
    }

    /**
     * Zalogování 404 chyby.
     */
    protected function logNotFound(Request $request): void
    {
        try {
            $path = '/' . ltrim($request->getPathInfo(), '/');

            // Ignorujeme některé zbytečné cesty (pokud je potřeba)
            // Např. telemetry, health checky atd.
            if ($path === '/up' || $path === '/health') {
                return;
            }

            $log = NotFoundLog::where('url', $path)
                ->where('is_ignored', false)
                ->first();

            if ($log) {
                $log->increment('hits_count');
                $log->update([
                    'last_seen_at' => now(),
                    'referer' => $request->header('referer') ?: $log->referer, // Udržíme poslední/původní referer
                    'user_agent' => $request->userAgent() ?: $log->user_agent,
                    'ip_address' => $request->ip() ?: $log->ip_address,
                ]);
            } else {
                NotFoundLog::create([
                    'url' => $path,
                    'referer' => $request->header('referer'),
                    'user_agent' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'hits_count' => 1,
                    'last_seen_at' => now(),
                    'status' => 'pending',
                    'is_ignored' => false,
                ]);
            }
        } catch (\Throwable $e) {
            // Tichý fail, nechceme aby logger shodil aplikaci
            \Illuminate\Support\Facades\Log::error('NotFoundLoggerMiddleware error: ' . $e->getMessage());
        }
    }
}
