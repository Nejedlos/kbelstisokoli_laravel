<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use App\Services\ScreenshotService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FeedbackDiagnosticCommand extends Command
{
    protected $signature = 'feedback:diagnostic';
    protected $description = 'Check requirements for the feedback system';

    public function handle()
    {
        $this->info('Starting Feedback System Diagnostic...');

        // 1. Check Node.js
        $this->line('Checking Node.js...');
        $node = config('feedback.screenshot.playwright.node_path', 'node');
        $proc = new Process([$node, '-v']);
        $proc->run();
        if ($proc->isSuccessful()) {
            $this->info('✓ Node.js version: ' . trim($proc->getOutput()));
        } else {
            $this->error('✗ Node.js not found or not executable: ' . $node);
        }

        // 2. Check Playwright dependency
        $this->line('Checking Playwright node_modules...');
        if (file_exists(base_path('node_modules/playwright'))) {
            $this->info('✓ playwright package found in node_modules.');
        } else {
            $this->error('✗ playwright package NOT found in node_modules. Run npm install.');
        }

        // 3. Check browsers
        $this->line('Checking browsers...');
        $env = [];
        if ($browsersPath = config('feedback.screenshot.playwright.browsers_path')) {
            $env['PLAYWRIGHT_BROWSERS_PATH'] = $browsersPath;
        }
        $proc = new Process([$node, '-e', 'require("playwright").chromium.launch()'], base_path(), $env);
        $proc->run();
        if ($proc->isSuccessful()) {
            $this->info('✓ Playwright can launch Chromium.');
        } else {
            $this->error('✗ Playwright CANNOT launch Chromium.');
            $this->error($proc->getErrorOutput());
            $this->line('Suggestion: Run npx playwright install chromium');
        }

        // 4. Check APP_URL and connectivity
        $this->line('Checking APP_URL connectivity...');
        $appUrl = config('app.url');
        $this->line('APP_URL: ' . $appUrl);
        $proc = new Process(['curl', '-I', $appUrl]);
        $proc->run();
        if ($proc->isSuccessful()) {
             $this->info('✓ Server can reach APP_URL via curl.');
        } else {
             $this->warn('! Server might have trouble reaching itself at APP_URL. This may break server-side screenshots.');
        }

        // 5. Check cache
        $this->line('Checking Cache...');
        $token = Str::random(10);
        Cache::put("diag_{$token}", 'test', 10);
        if (Cache::get("diag_{$token}") === 'test') {
            $this->info('✓ Cache is working.');
        } else {
            $this->error('✗ Cache is NOT working properly.');
        }

        // 6. Check storage
        $this->line('Checking Storage permissions...');
        $tempDir = storage_path('app/temp/screenshots');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }
        if (is_writable($tempDir)) {
            $this->info('✓ Storage temp dir is writable.');
        } else {
            $this->error('✗ Storage temp dir is NOT writable: ' . $tempDir);
        }

        $this->info('Diagnostic complete.');
    }
}
