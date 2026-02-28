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
        // Optimalizace: Nemusíme mazat cache, pokud se změnila jen technická pole PhotoPoolu,
        // což by se sice při použití updateQuietly() dít nemělo, ale jako pojistka je to vhodné.
        if ($model instanceof \App\Models\PhotoPool) {
            $technicalFields = ['is_processing_import', 'pending_import_queue', 'updated_at'];
            $dirtyFields = array_keys($model->getDirty());

            if (! empty($dirtyFields) && empty(array_diff($dirtyFields, $technicalFields))) {
                return;
            }
        }

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
        try {
            // 1. Mažeme statické klíče systémových nastavení
            // Používáme try-catch pro eliminaci SQL deadlocků na Webglobe hostingu
            Cache::forget('performance_settings');
            Cache::forget('view_composer_data');
            Cache::forget('global_branding_settings_cs');
            Cache::forget('global_branding_settings_en');
        } catch (\Throwable $e) {
            // V tichosti ignorujeme - uložení modelu je důležitější než okamžitý flush cache
        }

        // 2. Pro fragmenty a full-page cache (které mají dynamické klíče)
        // se pokusíme o cílené smazání v DB, pokud používáme database driver.
        // Tím předejdeme kompletnímu flush() celé cache, což na Webglobe
        // hostingu způsobuje lock wait timeouty a deadloky.
        if (config('cache.default') === 'database') {
            try {
                $table = config('cache.stores.database.table', 'cache');
                $prefix = config('cache.prefix', '');

                DB::table($table)
                    ->where('key', 'like', $prefix.'fragment_%')
                    ->orWhere('key', 'like', $prefix.'full_page_%')
                    ->delete();
            } catch (\Throwable $e) {
                // Fallback v případě chyby DB - v tichosti ignorujeme,
                // aby uložení modelu (např. článku) neselhalo kvůli cache.
            }
        } else {
            // Pro file driver na sdíleném hostingu (Webglobe) se vyhýbáme flush(),
            // který by mohl smazat i kritické soubory uprostřed requestu
            // v jiném vlákně/procesu (Race conditions).
            // Pokud je potřeba kompletní flush, měl by ho provést admin přes CLI.
        }
    }
}
