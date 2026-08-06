<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $this->measure('DB Latency (SELECT 1)', function () {
            DB::select('SELECT 1');
        });

        // 2. Storage Latency
        $this->measure('Storage Write/Read Latency', function () {
            Storage::put('perf-test.tmp', 'test');
            Storage::get('perf-test.tmp');
            Storage::delete('perf-test.tmp');
        });

        // 3. Framework Status
        $this->info("\n[FRAMEWORK STATUS]");
        $configCached = app()->configurationIsCached();
        $this->check('Config Cache', $configCached);
        $this->check('Route Cache', app()->routesAreCached());
        $this->check('View Cache', ! empty(glob(storage_path('framework/views/*.php'))));

        if ($configCached) {
            $viewCompiled = config('view.compiled');
            $realPath = storage_path('framework/views');
            if ($viewCompiled !== $realPath) {
                $this->error("ALARM: Config Cache obsahuje nekonzistentní cesty!");
                $this->line("Cachovaná cesta: $viewCompiled");
                $this->line("Reálná cesta:    $realPath");
                $this->warn("Doporučení: Spusťte 'php artisan config:clear'");
            }
        }

        // 4. Cache Status
        $this->info("\n[CACHE STATUS]");
        $this->check('Redis/File Cache', Cache::getStore() instanceof \Illuminate\Cache\FileStore || Cache::getStore() instanceof \Illuminate\Cache\RedisStore);
        $this->check('OPcache Active', function_exists('opcache_get_status') && opcache_get_status() !== false);

        // 4b. Database Indexes Status
        $this->info("\n[DATABASE INDEXES]");
        $this->checkIndex('matches', 'scheduled_at');
        $this->checkIndex('trainings', 'starts_at');
        $this->checkIndex('seasons', 'is_active');

        // 5. Statistics
        $this->info("\n[STORAGE STATISTICS]");
        $sessionCount = count(glob(storage_path('framework/sessions/*')));
        $viewCount = count(glob(storage_path('framework/views/*.php')));
        $this->line("Sessions: $sessionCount files");
        $this->line("Compiled Views: $viewCount files");

        // 6. Branding / Settings Check
        $this->info("\n[BRANDING & SETTINGS]");

        // 6.1 Cold Load (vynucené smazání cache)
        $this->measure('Branding Cold Load (Cache Clear)', function () {
            app(\App\Services\BrandingService::class)->clearCache();
            app(\App\Services\BrandingService::class)->getSettings();
        });

        // 6.2 Cached Load (načtení z cache)
        $this->measure('Branding Cached Load (Hit)', function () {
            app(\App\Services\BrandingService::class)->getSettings();
        });

        // 7. Partners Check (JSON decoding pressure)
        $this->info("\n[PARTNERS & JSON]");
        $this->measure('Partners Cold Load (Cache Clear)', function () {
            Cache::forget('partners_homepage_strip');
            app(\App\Services\PartnerService::class)->getHomepagePartners();
        });

        $this->measure('Partners Cached Load (Hit)', function () {
            app(\App\Services\PartnerService::class)->getHomepagePartners();
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

    protected function checkIndex($table, $column)
    {
        try {
            $schemaManager = DB::connection()->getSchemaBuilder();
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Column_name = '{$column}'");
            $exists = count($indexes) > 0;
            $this->check("Index {$table}.{$column}", $exists);
        } catch (\Throwable $e) {
            // Pro SQLite nebo jiné DB kde SHOW INDEX nefunguje
            $this->line(sprintf('%-40s: <comment>UNKNOWN (Requires MySQL)</comment>', "Index {$table}.{$column}"));
        }
    }

    protected function check($label, $condition)
    {
        $status = $condition ? '<info>ACTIVE</info>' : '<error>INACTIVE</error>';
        $this->line(sprintf('%-40s: %s', $label, $status));
    }
}
