<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PerformanceObserver
{
    /**
     * Handle the model "saved" event.
     */
    public function saved($model): void
    {
        $this->clearCache();
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted($model): void
    {
        $this->clearCache();
    }

    protected function clearCache(): void
    {
        // 1. Mažeme statické klíče systémových nastavení
        Cache::forget('performance_settings');
        Cache::forget('view_composer_data');
        Cache::forget('global_branding_settings_cs');
        Cache::forget('global_branding_settings_en');

        // 2. Pro fragmenty a full-page cache (které mají dynamické klíče)
        // se pokusíme o cílené smazání v DB, pokud používáme database driver.
        // Tím předejdeme kompletnímu flush() celé cache, což na Webglobe
        // hostingu způsobuje lock wait timeouty a deadloky.
        if (config('cache.default') === 'database') {
            try {
                $table = config('cache.stores.database.table', 'cache');
                $prefix = config('cache.prefix', '');

                DB::table($table)
                    ->where('key', 'like', $prefix . 'fragment_%')
                    ->orWhere('key', 'like', $prefix . 'full_page_%')
                    ->delete();
            } catch (\Throwable $e) {
                // Fallback v případě chyby DB - v tichosti ignorujeme,
                // aby uložení modelu (např. článku) neselhalo kvůli cache.
            }
        } else {
            // Pro ostatní drivery (Redis, File) můžeme použít flush().
            Cache::flush();
        }
    }
}
