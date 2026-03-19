<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class ScreenshotService
{
    /**
     * Generate screenshot via Playwright from provided DOM snippet using a secure snapshot route.
     * Returns array: [ 'data_url' => string, 'width' => int, 'height' => int, 'mime' => 'image/png', 'path' => string ]
     */
    public function captureViaPlaywrightFromDom(string $dom, array $options = []): array
    {
        $logContext = ['id' => Str::random(8)];
        Log::info('[ScreenshotService] Starting server-side capture', $logContext);

        if (!config('feedback.screenshot.playwright.enabled', true)) {
            Log::warning('[ScreenshotService] Playwright is disabled in config', $logContext);
            throw new \RuntimeException('Playwright disabled by config');
        }

        $node = $this->findCompatibleNode();
        if (!$node) {
             throw new \RuntimeException('Node.js >= 18 not found on the system. Required for Playwright.');
        }

        $ttl = $options['ttl'] ?? 120; // seconds
        $selector = $options['selector'] ?? '#snapshot-root';
        $viewport = $options['viewport'] ?? config('feedback.screenshot.playwright.viewports.desktop', ['width' => 1728, 'height' => 919]);
        $dpr = $options['dpr'] ?? 2;
        $fullPage = (bool)($options['fullPage'] ?? false);

        // 1) Create one-time token and cache DOM
        $token = Str::random(40);
        Log::debug('[ScreenshotService] Generating snapshot token', array_merge($logContext, ['token' => substr($token, 0, 8) . '...']));

        Cache::put("fb_snap_{$token}", [
            'dom' => $dom,
            'context' => $options['context'] ?? [],
        ], now()->addSeconds($ttl));

        // 2) Build URL to snapshot route
        $url = URL::to(route('feedback.snapshot', ['token' => $token], false));
        Log::debug('[ScreenshotService] Snapshot URL prepared', array_merge($logContext, ['url' => $url]));

        // 3) Prepare output file path
        $tempRelativeDir = trim(config('feedback.screenshot.playwright.temp_path', 'storage/app/temp/screenshots'), '/');
        $tempAbsDir = base_path($tempRelativeDir);
        if (!is_dir($tempAbsDir)) {
            @mkdir($tempAbsDir, 0755, true);
        }
        $filename = 'snap-' . Str::uuid() . '.png';
        $outAbs = $tempAbsDir . DIRECTORY_SEPARATOR . $filename;

        // 4) Run Node Playwright worker
        $script = base_path(config('feedback.screenshot.playwright.script_path', 'resources/js/screenshot-worker.cjs'));
        $timeoutMs = (int) config('feedback.screenshot.playwright.timeout', 30000);

        $args = [
            $node,
            $script,
            '--url=' . $url,
            '--selector=' . $selector,
            '--out=' . $outAbs,
            '--width=' . (int)$viewport['width'],
            '--height=' . (int)$viewport['height'],
            '--dpr=' . (float)$dpr,
            $fullPage ? '--fullPage=true' : '--fullPage=false',
        ];

        if ($chromiumPath = config('feedback.screenshot.playwright.chromium_path')) {
            $args[] = '--executablePath=' . $chromiumPath;
        }

        $timeoutSec = max(1, (int) ceil($timeoutMs / 1000));

        $nodePath = base_path('node_modules');
        if ($envNodePath = getenv('NODE_PATH')) {
            $nodePath .= PATH_SEPARATOR . $envNodePath;
        }

        $extraPaths = [
            base_path('node_modules/.bin'),
            base_path('vendor/bin'),
        ];

        $env = [
            'NODE_PATH' => $nodePath,
            'PATH' => implode(PATH_SEPARATOR, array_filter([
                getenv('PATH'),
                '/usr/local/bin',
                '/usr/bin',
                '/bin',
                ...$extraPaths
            ])),
        ];

        if (PHP_OS_FAMILY !== 'Darwin' && !getenv('PLAYWRIGHT_BROWSERS_PATH')) {
            $env['HOME'] = base_path('storage/app');
        }

        if ($browsersPath = config('feedback.screenshot.playwright.browsers_path')) {
            $env['PLAYWRIGHT_BROWSERS_PATH'] = $browsersPath;
        }

        Log::info('[ScreenshotService] Launching Playwright worker', array_merge($logContext, [
            'timeout' => $timeoutSec,
            'command' => implode(' ', $args),
            'node_path' => $nodePath
        ]));

        $proc = new Process($args, base_path(), $env);
        $proc->setTimeout($timeoutSec);
        $proc->run();

        if (!$proc->isSuccessful()) {
            $stderr = $proc->getErrorOutput();
            Log::error('[ScreenshotService] Playwright worker failed', array_merge($logContext, [
                'stderr' => $stderr,
                'exitCode' => $proc->getExitCode(),
            ]));
            throw new \RuntimeException('Playwright worker failed: ' . trim($stderr));
        }

        $stdout = trim($proc->getOutput());
        $json = [];
        try {
            $json = json_decode($stdout, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            Log::debug('[ScreenshotService] Could not parse worker stdout as JSON', array_merge($logContext, ['stdout' => $stdout]));
        }

        if (!file_exists($outAbs)) {
            Log::error('[ScreenshotService] Output file not found after successful process', $logContext);
            throw new \RuntimeException('Playwright did not produce output file.');
        }

        $image = file_get_contents($outAbs);
        $dataUrl = 'data:image/png;base64,' . base64_encode($image);

        Log::info('[ScreenshotService] Screenshot captured successfully', array_merge($logContext, [
            'size' => strlen($image),
            'dimensions' => ($json['width'] ?? '?') . 'x' . ($json['height'] ?? '?'),
        ]));

        return [
            'data_url' => $dataUrl,
            'mime' => 'image/png',
            'width' => $json['width'] ?? null,
            'height' => $json['height'] ?? null,
            'path' => $outAbs,
        ];
    }

    protected function findCompatibleNode(): ?string
    {
        $configured = config('feedback.screenshot.playwright.node_path', 'node');

        // Check if configured node is compatible
        if ($this->isNodeCompatible($configured)) {
            return $configured;
        }

        // Search for compatible node in common paths
        $commonPaths = [
            'node22', 'node20', 'node18', 'node', // Try PATH names first
            '/usr/local/bin/node22', '/usr/local/bin/node20', '/usr/local/bin/node18',
            '/usr/bin/node22', '/usr/bin/node20', '/usr/bin/node18',
            '/opt/node22/bin/node', '/opt/node20/bin/node', '/opt/node18/bin/node',
            '/usr/local/bin/node', '/usr/bin/node', '/bin/node',
        ];

        foreach ($commonPaths as $path) {
            if ($this->isNodeCompatible($path)) {
                Log::debug("[ScreenshotService] Found compatible Node.js binary: $path");
                return $path;
            }
        }

        return null;
    }

    protected function isNodeCompatible(string $node): bool
    {
        try {
            // Check if executable exists and get its version
            $proc = new Process([$node, '-v']);
            $proc->run();

            if (!$proc->isSuccessful()) {
                return false;
            }

            $versionStr = trim($proc->getOutput()); // v14.15.4 or v18.0.0
            $versionNum = (int) str_replace('v', '', $versionStr);

            return $versionNum >= 18;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function canExecute(string $command): bool
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return false; // Skip on Windows
        }
        $proc = new Process(['which', $command]);
        $proc->run();
        return $proc->isSuccessful();
    }
}
