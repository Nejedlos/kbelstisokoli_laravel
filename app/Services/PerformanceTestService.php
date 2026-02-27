<?php

namespace App\Services;

use App\Models\PerformanceTestResult;
use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PerformanceTestService
{
    protected array $scenarios = ['standard', 'aggressive', 'ultra'];

    /**
     * Spustí kompletní testovací suitu pro zadanou sekci.
     */
    public function runSectionTest(string $section, ?string $sessionId = null): void
    {
        $urls = $this->getUrlsForSection($section);

        foreach ($this->scenarios as $scenario) {
            foreach ($urls as $label => $url) {
                $this->runTest($scenario, $url, $label, $section, $sessionId);
            }
        }
    }

    /**
     * Spustí jednotlivý test pro zadaný scénář a URL.
     */
    public function runTest(string $scenario, string $url, string $label, string $section, ?string $sessionId = null): void
    {
        $fullUrl = url($url);
        // Přidáme query parametr pro vynucení scénáře (pokud je URL relativní)
        $fullUrl .= (str_contains($fullUrl, '?') ? '&' : '?') . 'perf_scenario=' . $scenario;

        $request = Http::withHeaders([
            'X-Performance-Test-Key' => config('app.key'),
            'Accept' => 'text/html',
        ]);

        if ($sessionId) {
            $request = $request->withCookies([
                config('session.cookie') => $sessionId,
            ], parse_url($fullUrl, PHP_URL_HOST));
        }

        try {
            $response = $request->get($fullUrl);

            // Získáme data z headerů, které přidává PerformanceProfilingMiddleware
            $duration = (float)$response->header('X-Perf-Duration-MS', 0);
            $queryCount = (int)$response->header('X-Perf-Query-Count', 0);
            $queryTime = (float)$response->header('X-Perf-Query-Time-MS', 0);
            $memory = (float)$response->header('X-Perf-Memory-MB', 0);
            $opcache = $response->header('X-Perf-Opcache') === 'enabled';

            if ($duration > 0) {
                PerformanceTestResult::create([
                    'scenario' => $scenario,
                    'url' => $url,
                    'label' => $label,
                    'section' => $section,
                    'duration_ms' => $duration,
                    'query_count' => $queryCount,
                    'query_time_ms' => $queryTime,
                    'memory_mb' => $memory,
                    'opcache_enabled' => $opcache,
                ]);
            } else {
                Log::error("Performance test failed: no duration header in response for $fullUrl. Status: " . $response->status());
            }
        } catch (\Throwable $e) {
            Log::error("Performance test failed for $fullUrl: " . $e->getMessage());
        }
    }

    protected function getUrlsForSection(string $section): array
    {
        switch ($section) {
            case 'public':
                $post = Post::where('status', 'published')->first();
                $urls = [
                    'Homepage' => '/',
                    'Novinky' => '/novinky',
                    'Zápasy' => '/zapasy',
                ];
                if ($post) {
                    $urls['Detail článku'] = '/novinky/' . $post->slug;
                }
                return $urls;

            case 'member':
                return [
                    'Dashboard' => '/clenska-sekce/dashboard',
                ];

            case 'admin':
                return [
                    'Dashboard' => '/admin',
                    'Uživatelé' => '/admin/users',
                ];

            default:
                return [];
        }
    }
}
