<?php

namespace App\Services\InternalAnalytics;

use App\Models\InternalAnalyticsEvent;
use App\Services\DeviceContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class InternalAnalyticsService
{
    public function trackRequest(Request $request, Response $response, array $context = []): void
    {
        try {
            if (! $this->shouldTrack($request)) {
                return;
            }

            $start = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
            $responseTime = (int) ((microtime(true) - $start) * 1000);
            $area = $this->resolveArea($request);
            $eventType = $this->resolveEventType($request, $response, $area);

            $data = [
                'event_type' => $eventType,
                'area' => $area,
                'method' => $request->method(),
                'path' => Str::limit($request->path(), 250),
                'route_name' => $request->route()?->getName(),
                'route_action' => $request->route()?->getActionName(),
                'full_url_hash' => hash('sha256', $request->fullUrl()),
                'status_code' => $response->getStatusCode(),
                'response_time_ms' => $responseTime,
                'user_id' => Auth::id(),
                'user_type' => Auth::check() ? get_class(Auth::user()) : null,
                'guard' => $this->resolveGuard(),
                'is_authenticated' => Auth::check(),
                'visitor_hash' => $this->makeVisitorHash($request),
                'session_hash' => $this->makeSessionHash($request),
                'ip_hash' => $this->makeIpHash($request),
                'user_agent' => Str::limit($request->userAgent(), 500),
                'referer' => Str::limit($request->headers->get('referer'), 500),
                'metadata' => array_merge($context, [
                    'device' => app(DeviceContextService::class)->collect($request),
                    'is_ajax' => $request->ajax(),
                    'is_pjax' => $request->pjax(),
                    'is_livewire' => $request->hasHeader('X-Livewire'),
                ]),
                'occurred_at' => now(),
            ];

            // Detekce pomalého requestu
            if ($responseTime >= config('internal-analytics.slow_request_threshold_ms', 1000)) {
                $data['metadata']['is_slow'] = true;
                $data['metadata']['slow_threshold_ms'] = config('internal-analytics.slow_request_threshold_ms', 1000);
            }

            // Detekce chyb
            if ($response->getStatusCode() >= 400) {
                $data['metadata']['is_error'] = true;
            }

            InternalAnalyticsEvent::create($data);
        } catch (\Exception $e) {
            Log::error('InternalAnalytics failed to track request: '.$e->getMessage(), [
                'exception' => $e,
                'path' => $request->path(),
            ]);
        }
    }

    public function trackEvent(string $eventType, array $data = []): void
    {
        try {
            if (! config('internal-analytics.enabled', true)) {
                return;
            }

            InternalAnalyticsEvent::create(array_merge([
                'event_type' => $eventType,
                'occurred_at' => now(),
            ], $data));
        } catch (\Exception $e) {
            Log::error('InternalAnalytics failed to track event: '.$e->getMessage(), [
                'event_type' => $eventType,
            ]);
        }
    }

    public function resolveArea(Request $request): string
    {
        $path = $request->path();
        $routeName = $request->route()?->getName();

        if (Str::startsWith($path, 'admin') || Str::startsWith($routeName, 'filament.')) {
            return 'admin';
        }

        if (Str::startsWith($path, 'clenska-sekce') || Str::startsWith($routeName, 'member.')) {
            return 'member';
        }

        if (Str::startsWith($path, 'api') || Str::startsWith($routeName, 'api.')) {
            return 'api';
        }

        if (Str::startsWith($routeName, 'public.')) {
            return 'frontend';
        }

        return 'unknown';
    }

    protected function resolveEventType(Request $request, Response $response, string $area): string
    {
        if ($request->hasHeader('X-Livewire') && config('internal-analytics.livewire_noise_filter_enabled', true)) {
            return 'livewire_update';
        }

        if ($request->isMethod('GET') && str_contains($response->headers->get('Content-Type'), 'text/html')) {
            return 'page_view';
        }

        return 'request';
    }

    public function makeVisitorHash(Request $request): ?string
    {
        $ip = $request->ip();
        $ua = $request->userAgent();
        $salt = config('internal-analytics.hash_salt');

        return hash('sha256', $ip.$ua.$salt);
    }

    public function makeSessionHash(Request $request): ?string
    {
        if (! $request->hasSession()) {
            return null;
        }

        return hash('sha256', $request->session()->getId().config('internal-analytics.hash_salt'));
    }

    public function makeIpHash(Request $request): ?string
    {
        $ip = $request->ip();
        if (! $ip) {
            return null;
        }

        return hash('sha256', $ip.config('internal-analytics.hash_salt'));
    }

    public function shouldTrack(Request $request): bool
    {
        if (! config('internal-analytics.enabled', true)) {
            return false;
        }

        // Sampling
        if (config('internal-analytics.sample_rate', 1.0) < 1.0) {
            if ((mt_rand() / mt_getrandmax()) > config('internal-analytics.sample_rate')) {
                return false;
            }
        }

        if ($this->isIgnoredMethod($request)) {
            return false;
        }
        if ($this->isIgnoredPath($request)) {
            return false;
        }
        if ($this->isIgnoredRoute($request)) {
            return false;
        }
        if ($this->isIgnoredExtension($request)) {
            return false;
        }
        if ($this->isBot($request)) {
            return false;
        }

        return true;
    }

    protected function isIgnoredMethod(Request $request): bool
    {
        return in_array(strtoupper($request->method()), config('internal-analytics.ignored_methods', []));
    }

    public function isIgnoredPath(Request $request): bool
    {
        $path = $request->path();
        foreach (config('internal-analytics.ignored_paths', []) as $ignored) {
            if (Str::is($ignored, $path)) {
                return true;
            }
        }

        return false;
    }

    public function isIgnoredRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();
        if (! $routeName) {
            return false;
        }

        foreach (config('internal-analytics.ignored_route_names', []) as $ignored) {
            if (Str::is($ignored, $routeName)) {
                return true;
            }
        }

        return false;
    }

    protected function isIgnoredExtension(Request $request): bool
    {
        $extension = pathinfo($request->path(), PATHINFO_EXTENSION);

        return in_array(strtolower($extension), config('internal-analytics.ignored_extensions', []));
    }

    public function isBot(Request $request): bool
    {
        if (! config('internal-analytics.bot_detection_enabled', true)) {
            return false;
        }

        $ua = $request->userAgent();
        if (! $ua) {
            return true;
        }

        foreach (config('internal-analytics.ignored_user_agents', []) as $bot) {
            if (stripos($ua, $bot) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function resolveGuard(): ?string
    {
        foreach (array_keys(config('auth.guards')) as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }
}
