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
     * Returns array: [ 'data_url' => string, 'width' => int, 'height' => int, 'mime' => 'image/png' ]
     */
    public function captureViaPlaywrightFromDom(string $dom, array $options = []): array
    {
        if (!config('feedback.screenshot.playwright.enabled', true)) {
            throw new \RuntimeException('Playwright disabled by config');
        }

        $ttl = $options['ttl'] ?? 120; // seconds
        $selector = $options['selector'] ?? '#snapshot-root';
        $viewport = $options['viewport'] ?? ['width' => 1728, 'height' => 919];
        $dpr = $options['dpr'] ?? 2;
        $fullPage = (bool)($options['fullPage'] ?? false);

        // 1) Create one-time token and cache DOM
        $token = Str::random(40);
        Cache::put("fb_snap_{$token}", [
            'dom' => $dom,
            'context' => $options['context'] ?? [],
        ], now()->addSeconds($ttl));

        // 2) Build URL to snapshot route
        $url = URL::to(route('feedback.snapshot', ['token' => $token], false));

        // 3) Prepare output file path
        $tempRelativeDir = trim(config('feedback.screenshot.playwright.temp_path', 'storage/app/temp/screenshots'), '/');
        $tempAbsDir = base_path($tempRelativeDir);
        if (!is_dir($tempAbsDir)) {
            @mkdir($tempAbsDir, 0755, true);
        }
        $filename = 'snap-' . Str::uuid() . '.png';
        $outAbs = $tempAbsDir . DIRECTORY_SEPARATOR . $filename;

        // 4) Run Node Playwright worker
        $node = config('feedback.screenshot.playwright.node_path', 'node');

        // Simple heuristic for common paths if 'node' is not found in PATH
        if ($node === 'node' && !$this->canExecute($node)) {
            $commonPaths = ['/usr/local/bin/node', '/opt/homebrew/bin/node', '/usr/bin/node'];
            foreach ($commonPaths as $p) {
                if (file_exists($p) && is_executable($p)) {
                    $node = $p;
                    break;
                }
            }
        }

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

        $timeoutSec = max(1, (int) ceil($timeoutMs / 1000));
        $proc = new Process($args, base_path());
        $proc->setTimeout($timeoutSec);
        $proc->run();

        if (!$proc->isSuccessful()) {
            $stderr = $proc->getErrorOutput();
            Log::warning('Playwright screenshot failed', [
                'stderr' => $stderr,
                'args' => $args,
            ]);
            throw new \RuntimeException('Playwright worker failed: ' . trim($stderr));
        }

        $stdout = trim($proc->getOutput());
        $json = [];
        try {
            $json = json_decode($stdout, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            // ignore, we can still read file directly
        }

        if (!file_exists($outAbs)) {
            throw new \RuntimeException('Playwright did not produce output file.');
        }

        $image = file_get_contents($outAbs);
        $dataUrl = 'data:image/png;base64,' . base64_encode($image);

        // Cleanup could be deferred; we keep file for debugging for now
        $meta = [
            'data_url' => $dataUrl,
            'mime' => 'image/png',
            'width' => $json['width'] ?? null,
            'height' => $json['height'] ?? null,
            'path' => $outAbs,
        ];

        return $meta;
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
