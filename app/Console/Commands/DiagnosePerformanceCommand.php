<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

class DiagnosePerformanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:perf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnostika a monitoring výkonu systému.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->header('KBELŠTÍ SOKOLI - PERFORMANCE DIAGNOSIS');

        // 1. DB Query speed
        $this->measure('DB Latency (SELECT 1)', function() {
            DB::select('SELECT 1');
        });

        // 2. Storage Latency
        $this->measure('Storage Write/Read Latency', function() {
            Storage::put('perf-test.tmp', 'test');
            Storage::get('perf-test.tmp');
            Storage::delete('perf-test.tmp');
        });

        // 3. Framework Status
        $this->info("\n[FRAMEWORK STATUS]");
        $this->check('Config Cache', config()->has('app.name')); // Vždy true, ale config:cache zrychluje načítání
        $this->check('Route Cache', app()->routesAreCached());
        $this->check('View Cache', !empty(glob(storage_path('framework/views/*.php'))));

        // 4. Cache Status
        $this->info("\n[CACHE STATUS]");
        $this->check('Redis/File Cache', Cache::getStore() instanceof \Illuminate\Cache\FileStore || Cache::getStore() instanceof \Illuminate\Cache\RedisStore);
        $this->check('OPcache Active', function_exists('opcache_get_status') && opcache_get_status() !== false);

        // 5. Statistics
        $this->info("\n[STORAGE STATISTICS]");
        $sessionCount = count(glob(storage_path('framework/sessions/*')));
        $viewCount = count(glob(storage_path('framework/views/*.php')));
        $this->line("Sessions: $sessionCount files");
        $this->line("Compiled Views: $viewCount files");

        // 6. Branding / Settings Check
        $this->info("\n[BRANDING & SETTINGS]");
        $this->measure('Branding Settings Load', function() {
            app(\App\Services\BrandingService::class)->clearCache();
            app(\App\Services\BrandingService::class)->getSettings();
        });

        $this->line("\n[DONE] Diagnostika dokončena.");
        return 0;
    }

    protected function header($text)
    {
        $this->line(str_repeat('=', strlen($text)));
        $this->info($text);
        $this->line(str_repeat('=', strlen($text)));
    }

    protected function measure($label, $callback)
    {
        $start = microtime(true);
        $callback();
        $end = microtime(true);
        $duration = round(($end - $start) * 1000, 2);

        $status = $duration < 50 ? 'OK' : ($duration < 200 ? 'WARNING' : 'SLOW');
        $color = $duration < 50 ? 'info' : ($duration < 200 ? 'comment' : 'error');

        $this->line(sprintf("%-40s: <$color>%8.2f ms</$color> [%s]", $label, $duration, $status));
    }

    protected function check($label, $condition)
    {
        $status = $condition ? '<info>ACTIVE</info>' : '<error>INACTIVE</error>';
        $this->line(sprintf("%-40s: %s", $label, $status));
    }
}
