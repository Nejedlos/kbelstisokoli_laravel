<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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
        if (!$this->shouldProfile($request)) {
            return $next($request);
        }

        $startTime = microtime(true);

        // Zapnutí query logu
        DB::enableQueryLog();

        $response = $next($request);

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // v ms

        $queries = DB::getQueryLog();
        $queryCount = count($queries);
        $queryTime = array_sum(array_column($queries, 'time'));

        $memoryPeak = memory_get_peak_usage(true) / 1024 / 1024; // v MB

        $logData = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
            'query_count' => $queryCount,
            'query_time_ms' => round($queryTime, 2),
            'memory_mb' => round($memoryPeak, 2),
            'route' => $request->route() ? $request->route()->getName() : 'n/a',
        ];

        // Pokud je to pomalý request (např. > 500ms) nebo hodně queries (např. > 50), logujeme to výrazněji
        if ($duration > 500 || $queryCount > 50) {
            $duplicatedQueries = $this->getDuplicatedQueries($queries);
            $logData['duplicated_queries'] = $duplicatedQueries;
            Log::warning('Slow request detected', $logData);
        } else {
            Log::info('Performance profile', $logData);
        }

        // Přidání headers pro snadnou diagnostiku v DevTools (pouze pro autorizované uživatele nebo v local prostředí)
        if (app()->environment('local') || $this->isAuthorized($request)) {
            $response->headers->set('X-Perf-Duration-MS', round($duration, 2));
            $response->headers->set('X-Perf-Query-Count', $queryCount);
            $response->headers->set('X-Perf-Query-Time-MS', round($queryTime, 2));
            $response->headers->set('X-Perf-Memory-MB', round($memoryPeak, 2));

            // Diagnostika Opcache
            $opcacheEnabled = function_exists('opcache_get_status') && opcache_get_status(false);
            $response->headers->set('X-Perf-Opcache', $opcacheEnabled ? 'enabled' : 'disabled');
        }

        return $response;
    }

    protected function shouldProfile(Request $request): bool
    {
        // Profilujeme v lokálním prostředí, pro autorizované uživatele nebo pro interní testy
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
        // Kontrola, zda je uživatel admin (používáme Spatie Permission nebo jinou logiku)
        // V tomto projektu se zdá, že existuje oprávnění 'access_admin'
        return Auth::check() && $request->user()->can('access_admin');
    }

    protected function getDuplicatedQueries(array $queries): array
    {
        $counts = [];
        foreach ($queries as $query) {
            $sql = $query['query'];
            // Nahradíme parametry pro lepší agregaci
            $sql = preg_replace('/(\'|").*?(\'|")/', '?', $sql);
            $sql = preg_replace('/\d+/', '?', $sql);

            if (!isset($counts[$sql])) {
                $counts[$sql] = 0;
            }
            $counts[$sql]++;
        }

        arsort($counts);
        $counts = array_filter($counts, fn($count) => $count > 1);

        return array_slice($counts, 0, 10, true);
    }
}
