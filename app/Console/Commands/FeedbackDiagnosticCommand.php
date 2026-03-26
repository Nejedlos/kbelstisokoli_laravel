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
        $this->info('Starting Feedback System Diagnostic (Remote Service)...');

        // 1. Check Configuration
        $this->line('Checking Configuration...');
        $url = config('services.screenshot.url');
        $token = config('services.screenshot.token');

        if ($url) {
            $this->info('✓ Screenshot Service URL: ' . $url);
        } else {
            $this->error('✗ Screenshot Service URL NOT configured (SCREENSHOT_SERVICE_URL).');
        }

        if ($token) {
            $this->info('✓ Screenshot Service Token: ' . substr($token, 0, 8) . '...');
        } else {
            $this->error('✗ Screenshot Service Token NOT configured (SCREENSHOT_SERVICE_TOKEN).');
        }

        // 2. Check Service Health (if URL exists)
        if ($url) {
            $this->line('Checking Remote Service Health...');
            try {
                // Assuming the service has a /health endpoint or we just check connectivity
                $healthUrl = str_replace('/screenshot', '/health', $url);
                $response = \Illuminate\Support\Facades\Http::get($healthUrl);

                if ($response->successful()) {
                    $this->info('✓ Remote service is UP and reachable.');
                } else {
                    $this->warn('! Remote service returned status: ' . $response->status());
                    $this->line('Trying connectivity to the main URL...');
                    $response = \Illuminate\Support\Facades\Http::withToken($token)->post($url, ['url' => 'https://google.com', 'width' => 10, 'height' => 10]);
                    if ($response->status() !== 404 && $response->status() !== 500) {
                        $this->info('✓ Remote service seems reachable (Status: ' . $response->status() . ')');
                    } else {
                        $this->error('✗ Remote service connectivity failed.');
                    }
                }
            } catch (\Exception $e) {
                $this->error('✗ Cannot reach remote service: ' . $e->getMessage());
            }
        }

        // 3. Check APP_URL and connectivity
        $this->line('Checking APP_URL connectivity...');
        $appUrl = config('app.url');
        $this->line('APP_URL: ' . $appUrl);
        $proc = new Process(['curl', '-I', $appUrl]);
        $proc->run();
        if ($proc->isSuccessful()) {
             $this->info('✓ Server can reach APP_URL via curl.');
        } else {
             $this->warn('! Server might have trouble reaching itself at APP_URL. The remote service MUST be able to reach this URL.');
        }

        // 4. Check cache
        $this->line('Checking Cache (for DOM snapshot)...');
        $token = Str::random(10);
        Cache::put("diag_{$token}", 'test', 10);
        if (Cache::get("diag_{$token}") === 'test') {
            $this->info('✓ Cache is working.');
        } else {
            $this->error('✗ Cache is NOT working properly.');
        }

        $this->info('Diagnostic complete.');
    }
}
