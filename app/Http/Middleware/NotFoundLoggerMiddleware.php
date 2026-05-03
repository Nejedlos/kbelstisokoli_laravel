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
            $path = '/'.ltrim($request->getPathInfo(), '/');

            // Ignorujeme statické soubory, které často generují 404 (ikony, mapy atd.)
            if (preg_match('/\.(png|jpg|jpeg|gif|svg|webp|ico|css|js|map)$/i', $path)) {
                return;
            }

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
                    'referer' => substr($request->header('referer'), 0, 255) ?: $log->referer, // Udržíme poslední/původní referer
                    'user_agent' => substr($request->userAgent(), 0, 255) ?: $log->user_agent,
                    'ip_address' => $request->ip() ?: $log->ip_address,
                ]);
            } else {
                NotFoundLog::create([
                    'url' => substr($path, 0, 255),
                    'referer' => substr($request->header('referer'), 0, 255),
                    'user_agent' => substr($request->userAgent(), 0, 255),
                    'ip_address' => $request->ip(),
                    'hits_count' => 1,
                    'last_seen_at' => now(),
                    'status' => 'pending',
                    'is_ignored' => false,
                ]);
            }
        } catch (\Throwable $e) {
            // Tichý fail, nechceme aby logger shodil aplikaci
            \Illuminate\Support\Facades\Log::error('NotFoundLoggerMiddleware error: '.$e->getMessage());
        }
    }
}
