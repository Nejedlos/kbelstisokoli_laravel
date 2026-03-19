<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class PerformanceProfilingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $bootstrapDuration = defined('LARAVEL_START') ? ($startTime - LARAVEL_START) * 1000 : 0;

        // Zapnutí query logu co nejdříve (pouze pokud nejsme na produkci nebo jsme v profilačním módu)
        $shouldProfile = $this->shouldProfile($request);
        try {
            if ($shouldProfile && !DB::getQueryLog()) {
                DB::enableQueryLog();
            }
        } catch (\Throwable $e) {
            // Ignorujeme, pokud DB není připravena
        }

        $response = $next($request);

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // v ms
        $totalTime = $duration + $bootstrapDuration;

        $queries = [];
        if ($shouldProfile) {
            try {
                $queries = DB::getQueryLog();
            } catch (\Throwable $e) {}
        }

        $queryCount = count($queries);
        $queryTime = array_sum(array_column($queries, 'time'));
        $memoryPeak = memory_get_peak_usage(true) / 1024 / 1024; // v MB
        $opcacheStatus = function_exists('opcache_get_status') ? opcache_get_status(false) : false;
        $opcacheEnabled = $opcacheStatus && (is_array($opcacheStatus) ? ($opcacheStatus['opcache_enabled'] ?? false) : false);

        $logData = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status' => $response->getStatusCode(),
            'bootstrap_ms' => round($bootstrapDuration, 2),
            'logic_ms' => round($duration, 2),
            'total_ms' => round($totalTime, 2),
            'query_count' => $queryCount,
            'query_time_ms' => round($queryTime, 2),
            'memory_mb' => round($memoryPeak, 2),
            'route' => $request->route() ? $request->route()->getName() : 'n/a',
            'opcache' => $opcacheEnabled ? 'on' : 'off',
            'opcache_info' => $opcacheStatus ? 'ext_loaded' : 'ext_missing',
        ];

        // Rozlišení typu požadavku pro logy
        $isLivewire = $request->hasHeader('X-Livewire');
        $isXhr = $request->ajax() || $request->wantsJson();

        $shouldLog = $shouldProfile || $totalTime > 1000 || $queryCount > 50;

        if ($shouldLog) {
            $prefix = "[PERF] ";
            if ($totalTime > 2000 || $queryCount > 100) {
                $prefix = "[SLOW] ";
                $duplicatedQueries = $this->getDuplicatedQueries($queries);
                if ($duplicatedQueries) {
                    $logData['duplicated_queries'] = $duplicatedQueries;
                }
                Log::warning($prefix . "Request: {$request->method()} {$request->path()}, Duration: " . round($totalTime, 2) . "ms, Queries: {$queryCount}", $logData);
            } else {
                Log::info($prefix . "Request: {$request->method()} {$request->path()}, Duration: " . round($totalTime, 2) . "ms", $logData);
            }
        }

        // Přidání headers pro snadnou diagnostiku v DevTools
        // Vždy přidáme základní časy, pokud jsme v profilačním módu nebo jsme v debug módu na lokálu
        if ($shouldProfile || (config('app.debug') && app()->isLocal())) {
            $response->headers->set('X-Perf-Bootstrap-MS', round($bootstrapDuration, 2));
            $response->headers->set('X-Perf-Logic-MS', round($duration, 2));
            $response->headers->set('X-Perf-Total-MS', round($totalTime, 2));
            $response->headers->set('X-Perf-Queries-Count', $queryCount);
            $response->headers->set('X-Perf-Queries-MS', round($queryTime, 2));
            $response->headers->set('X-Perf-Memory-MB', round($memoryPeak, 2));
            $response->headers->set('X-Perf-OPcache', $opcacheEnabled ? 'On' : 'Off');

            $duplicatedQueries = $this->getDuplicatedQueries($queries);
            if (!empty($duplicatedQueries)) {
                $response->headers->set('X-Perf-Queries-Duplicated', count($duplicatedQueries));
            }
        }

        return $response;
    }

    protected function shouldProfile(Request $request): bool
    {
        // Profilujeme v lokálním prostředí, pro interní testy nebo pokud je to pomalé (ošetřeno v handle)
        if (app()->environment('local')) {
            return true;
        }

        if ($request->header('X-Performance-Test-Key') === config('app.key')) {
            return true;
        }

        return $this->isAuthorized($request);
    }

    protected function isAuthorized(Request $request): bool
    {
        // Kontrola, zda je uživatel admin.
        // POZOR: Pokud middleware běží PŘED StartSession, neuvidíme autentizaci.
        // V tom případě spoléháme na by-time logging v handle().
        try {
            if ($request->hasSession() && $request->session()->has('impersonated_by')) {
                return true;
            }
        } catch (\Throwable $e) {
        }

        try {
            return Auth::check() && $request->user()?->can('access_admin');
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function getDuplicatedQueries(array $queries): array
    {
        $counts = [];
        foreach ($queries as $query) {
            $sql = $query['query'];
            // Nahradíme parametry pro lepší agregaci
            $sql = preg_replace('/(\'|").*?(\'|")/', '?', $sql);
            $sql = preg_replace('/\d+/', '?', $sql);

            if (! isset($counts[$sql])) {
                $counts[$sql] = 0;
            }
            $counts[$sql]++;
        }

        arsort($counts);
        $counts = array_filter($counts, fn ($count) => $count > 1);

        return array_slice($counts, 0, 10, true);
    }
}
