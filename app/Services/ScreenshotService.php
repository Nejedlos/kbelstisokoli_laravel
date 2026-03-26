<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Exception;

class ScreenshotService
{
    protected ?string $driver;
    protected ?string $url;
    protected ?string $token;
    protected int $timeout;
    protected array $browsershotConfig;

    public function __construct()
    {
        $this->driver = config('services.screenshot.driver', 'remote');
        $this->url = config('services.screenshot.url');
        $this->token = config('services.screenshot.token');
        $this->timeout = (int) config('services.screenshot.timeout', 40);
        $this->browsershotConfig = config('services.screenshot.browsershot', []);

        // Pokud jsme na localu a nemáme nastavený driver, přepneme na local
        if (app()->environment('local') && empty(env('SCREENSHOT_DRIVER'))) {
            $this->driver = 'local';
        }
    }

    /**
     * Generate screenshot via remote Playwright service or local Browsershot from provided DOM snippet using a secure snapshot route.
     * Returns array: [ 'data_url' => string, 'width' => int, 'height' => int, 'mime' => 'image/png', 'path' => string|null ]
     */
    public function captureViaPlaywrightFromDom(string $dom, array $options = []): array
    {
        $logContext = ['id' => Str::random(8), 'driver' => $this->driver];

        if ($this->driver === 'local') {
            return $this->captureLocally($dom, $options);
        }

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
            $headers = $this->prepareHeaders($options);

            Log::debug('[ScreenshotService] Calling remote API (Playwright)', [
                'service_url' => $this->url,
                'target_url' => $targetUrl,
                'token_prefix' => substr($this->token, 0, 10) . '...',
                'timeout' => $this->timeout,
                'fullPage' => $fullPage,
                'selector' => $selector,
                'headers' => array_keys($headers), // Log only keys for security
            ]);

            $response = Http::withoutVerifying()
                ->withToken($this->token)
                ->timeout($this->timeout)
                ->retry(2, 5000)
                ->post($this->url, [
                    'url'      => $targetUrl,
                    'fullPage' => $fullPage,
                    'width'    => (int) $viewport['width'],
                    'height'   => (int) $viewport['height'],
                    'selector' => $selector,
                    'headers'  => $headers,
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
                'response' => $response->body(),
                'url'     => $targetUrl
            ]));

            $errorMessage = $response->json('message') ?? $response->statusText() ?? 'Unknown API Error';
            throw new \RuntimeException("Remote Screenshot API Error ({$response->status()}): {$errorMessage}");

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
        if ($this->driver === 'local') {
            $result = $this->captureLocally('', array_merge($options, ['url' => $targetUrl]));
            if ($result && isset($result['data_url'])) {
                // Převod zpět z base64 data_url na binární data
                return base64_decode(explode(',', $result['data_url'])[1]);
            }
            return null;
        }

        try {
            $headers = $this->prepareHeaders($options);
            $userId = $options['context']['user_id'] ?? null;

            if ($userId) {
                $targetUrl = $this->appendImpersonationParams($targetUrl, $userId);
            }

            Log::debug('[ScreenshotService] Calling remote API (URL)', [
                'service_url' => $this->url,
                'target_url' => $targetUrl,
                'token_prefix' => substr($this->token, 0, 10) . '...',
                'timeout' => $this->timeout,
                'headers' => array_keys($headers),
            ]);

            $response = Http::withoutVerifying()
                ->withToken($this->token)
                ->timeout($this->timeout)
                ->retry(2, 5000)
                ->post($this->url, array_merge([
                    'url'      => $targetUrl,
                    'fullPage' => false,
                    'width'    => 1280,
                    'height'   => 720,
                    'headers'  => $headers,
                    'type'     => 'png',
                ], $options));

            if ($response->successful()) {
                Log::info('[ScreenshotService] Screenshot captured successfully via remote service (URL)');
                return $response->body();
            }

            Log::error('[ScreenshotService] Remote Screenshot API Error (URL)', [
                'status'  => $response->status(),
                'response' => $response->body(),
                'url'     => $targetUrl
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('[ScreenshotService] Remote Screenshot Service Exception (URL)', [
                'error' => $e->getMessage(),
                'url'   => $targetUrl
            ]);
            return null;
        }
    }

    /**
     * Internal method to capture via local driver (using Playwright script).
     */
    protected function captureLocally(string $dom, array $options = []): array
    {
        $logContext = ['driver' => 'local'];
        Log::info('[ScreenshotService] Starting local capture (Playwright JS)', $logContext);

        try {
            $viewport = $options['viewport'] ?? ['width' => 1280, 'height' => 720];
            $targetUrl = $options['url'] ?? config('app.url');

            // Autentizační hlavičky a query parametry
            $userId = $options['context']['user_id'] ?? null;
            $headers = $this->prepareHeaders($options);

            // Přidáme user_id do URL pro impersonifikaci
            if ($userId) {
                $targetUrl = $this->appendImpersonationParams($targetUrl, $userId);
            }

            // Příprava argumentů pro JS script
            $tempFile = storage_path('app/temp-screenshot-' . Str::random(10) . '.png');
            $jsOptions = [
                'width'          => (int) $viewport['width'],
                'height'         => (int) $viewport['height'],
                'executablePath' => $this->browsershotConfig['chrome_path'] ?? null,
                'headers'        => $headers,
                'selector'       => $options['selector'] ?? null,
                'waitUntil'      => 'networkidle'
            ];

            $nodeBin = $this->browsershotConfig['node_path'] ?? 'node';
            $scriptPath = app_path('Support/local-screenshot.cjs');

            // Zajistíme, aby v PATH byly běžné cesty k node pro Mac (Homebrew atd.)
            $nodeDir = is_executable($nodeBin) ? dirname($nodeBin) : '/opt/homebrew/bin';

            $command = sprintf(
                'PATH=$PATH:%s:/usr/local/bin NODE_PATH=%s %s %s %s %s %s 2>&1',
                escapeshellarg($nodeDir),
                escapeshellarg(base_path('node_modules')),
                escapeshellarg($nodeBin),
                escapeshellarg($scriptPath),
                escapeshellarg($targetUrl),
                escapeshellarg($tempFile),
                escapeshellarg(json_encode($jsOptions))
            );

            Log::debug('[ScreenshotService] Executing local command', array_merge($logContext, ['command' => $command]));

            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            if ($returnVar !== 0 || !file_exists($tempFile)) {
                $errorMsg = implode("\n", $output);
                Log::error('[ScreenshotService] Local capture failed', array_merge($logContext, ['error' => $errorMsg]));
                throw new \Exception("Local screenshot failed: " . $errorMsg);
            }

            $imageContent = file_get_contents($tempFile);
            unlink($tempFile);

            $base64 = base64_encode($imageContent);

            return [
                'data_url' => 'data:image/png;base64,' . $base64,
                'mime' => 'image/png',
                'width' => (int) $viewport['width'],
                'height' => (int) $viewport['height'],
                'path' => null,
            ];

        } catch (\Exception $e) {
            Log::error('[ScreenshotService] local capture exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Helper to prepare headers for screenshot request.
     */
    protected function prepareHeaders(array $options = []): array
    {
        $headers = [
            'X-Screenshot-Mode' => '1',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
        ];

        $token = config('screenshot.internal_token');
        if ($token) {
            $headers['X-Screenshot-Token'] = $token;
        }

        return array_merge($headers, $options['headers'] ?? []);
    }

    /**
     * Helper to append impersonation parameters to URL.
     */
    protected function appendImpersonationParams(string $url, $userId): string
    {
        $parsed = parse_url($url);
        $query = $parsed['query'] ?? '';
        parse_str($query, $queryParams);
        $queryParams['screenshot_user_id'] = $userId;
        $queryParams['screenshot'] = '1';
        $newQuery = http_build_query($queryParams);

        return (isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '') .
               (isset($parsed['host']) ? $parsed['host'] : '') .
               (isset($parsed['port']) ? ':' . $parsed['port'] : '') .
               (isset($parsed['path']) ? $parsed['path'] : '') .
               ($newQuery ? '?' . $newQuery : '') .
               (isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '');
    }

    /**
     * Apply common Browsershot configurations.
     */
    protected function applyBrowsershotConfig(Browsershot $browsershot, array $options = []): void
    {
        $browsershot->windowSize($options['width'] ?? 1280, $options['height'] ?? 720);

        if ($options['fullPage'] ?? false) {
            $browsershot->fullPage();
        }

        if ($selector = ($options['selector'] ?? null)) {
            $browsershot->select($selector);
        }

        if ($this->browsershotConfig['chrome_path'] ?? null) {
            $browsershot->setChromePath($this->browsershotConfig['chrome_path']);
        }

        if ($this->browsershotConfig['node_path'] ?? null) {
            $browsershot->setNodePath($this->browsershotConfig['node_path']);
            // Přidáme cestu k node binárce do PATH, aby Browsershot mohl spustit node i npm
            $nodeBinDir = dirname($this->browsershotConfig['node_path']);
            $currentPath = getenv('PATH') ?: '/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin';
            $browsershot->setIncludePath($nodeBinDir . PATH_SEPARATOR . '/opt/homebrew/bin' . PATH_SEPARATOR . '/usr/local/bin' . PATH_SEPARATOR . $currentPath);
        }

        if ($this->browsershotConfig['npm_path'] ?? null) {
            $browsershot->setNpmPath($this->browsershotConfig['npm_path']);
        }

        $browsershot->noSandbox()
            ->waitUntilNetworkIdle()
            ->setOption('args', ['--disable-web-security', '--disable-setuid-sandbox']);
    }
}
