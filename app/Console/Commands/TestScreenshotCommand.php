<?php

namespace App\Console\Commands;

use App\Services\ScreenshotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestScreenshotCommand extends Command
{
    protected $signature = 'test:screenshot {url=https://kbelstisokoli.cz}';
    protected $description = 'Test the remote screenshot service';

    public function handle(ScreenshotService $service)
    {
        $url = $this->argument('url');
        $this->info("Requesting screenshot for: {$url}");

        $result = $service->capture($url);

        if ($result) {
            $path = 'screenshots/test-manual.png';
            Storage::disk('public')->put($path, $result);
            $this->info("Screenshot saved to: storage/app/public/{$path}");
        } else {
            $this->error("Screenshot failed - check logs.");
        }
    }
}
