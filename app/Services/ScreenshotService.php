<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Exception;

class ScreenshotService
{
    protected ?string $url;
    protected ?string $token;
    protected int $timeout;

    public function __construct()
    {
        $this->url = config('services.screenshot.url');
        $this->token = config('services.screenshot.token');
        $this->timeout = (int) config('services.screenshot.timeout', 40);
    }

    /**
     * Generate screenshot via remote Playwright service from provided DOM snippet using a secure snapshot route.
     * Returns array: [ 'data_url' => string, 'width' => int, 'height' => int, 'mime' => 'image/png', 'path' => string|null ]
     */
    public function captureViaPlaywrightFromDom(string $dom, array $options = []): array
    {
        $logContext = ['id' => Str::random(8)];
        Log::info('[ScreenshotService] Starting remote capture from DOM', $logContext);

        if (empty($this->url) || empty($this->token)) {
            Log::error('[ScreenshotService] Remote service not configured', $logContext);
            throw new \RuntimeException('Screenshot service not configured (missing URL or Token)');
        }

        $ttl = $options['ttl'] ?? 300; // 5 minutes
        $selector = $options['selector'] ?? '#snapshot-root';
        $viewport = $options['viewport'] ?? ['width' => 1280, 'height' => 720];
        $fullPage = (bool)($options['fullPage'] ?? false);

        // 1) Create one-time token and cache DOM
        $token = Str::random(40);
        Log::debug('[ScreenshotService] Generating snapshot token', array_merge($logContext, ['token' => substr($token, 0, 8) . '...']));

        Cache::put("fb_snap_{$token}", [
            'dom' => $dom,
            'context' => $options['context'] ?? [],
        ], now()->addSeconds($ttl));

        // 2) Build URL to snapshot route
        // IMPORTANT: The URL must be accessible from the NAS service.
        $targetUrl = route('feedback.snapshot', ['token' => $token]);

        Log::debug('[ScreenshotService] Snapshot URL prepared', array_merge($logContext, ['url' => $targetUrl]));

        // 3) Call remote service
        try {
            $response = Http::withToken($this->token)
                ->timeout($this->timeout)
                ->retry(2, 5000)
                ->post($this->url, [
                    'url'      => $targetUrl,
                    'fullPage' => $fullPage,
                    'width'    => (int) $viewport['width'],
                    'height'   => (int) $viewport['height'],
                    'selector' => $selector,
                    'type'     => 'png',
                ]);

            if ($response->successful()) {
                $imageContent = $response->body();
                $base64 = base64_encode($imageContent);
                $dataUrl = 'data:image/png;base64,' . $base64;

                Log::info('[ScreenshotService] Screenshot captured successfully via remote service', array_merge($logContext, [
                    'size' => strlen($imageContent),
                ]));

                return [
                    'data_url' => $dataUrl,
                    'mime' => 'image/png',
                    'width' => (int) $viewport['width'],
                    'height' => (int) $viewport['height'],
                    'path' => null, // Remote service doesn't save to local path directly
                ];
            }

            Log::error('[ScreenshotService] Remote Screenshot API Error', array_merge($logContext, [
                'status'  => $response->status(),
                'message' => $response->json('message'),
                'url'     => $targetUrl
            ]));

            throw new \RuntimeException('Remote Screenshot API Error: ' . ($response->json('message') ?? 'Unknown error'));

        } catch (Exception $e) {
            Log::error('[ScreenshotService] Remote Screenshot Service Exception', array_merge($logContext, [
                'error' => $e->getMessage(),
                'url'   => $targetUrl
            ]));
            throw $e;
        }
    }

    /**
     * Pořídí screenshot libovolné URL a vrátí binární data (pro zpětnou kompatibilitu s návodem).
     */
    public function capture(string $targetUrl, array $options = []): ?string
    {
        try {
            $response = Http::withToken($this->token)
                ->timeout($this->timeout)
                ->retry(2, 5000)
                ->post($this->url, array_merge([
                    'url'      => $targetUrl,
                    'fullPage' => false,
                    'width'    => 1280,
                    'height'   => 720,
                    'type'     => 'png',
                ], $options));

            if ($response->successful()) {
                return $response->body();
            }

            return null;
        } catch (Exception $e) {
            Log::error('[ScreenshotService] capture exception', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
