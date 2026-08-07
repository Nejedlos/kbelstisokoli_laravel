<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PerformanceService
{
    protected ?array $settings = null;

    /**
     * Načte nastavení výkonu z DB a zaktualizuje config za běhu.
     */
    public function bootSettings(): void
    {
        $settings = $this->getSettings();

        // Výchozí scénář: .env má přednost, pak DB, pak auto-detekce podle prostředí
        $scenario = config('performance.scenario', $settings['perf_scenario'] ?? (app()->isProduction() ? 'ultra' : 'standard'));

        // Pokud jsme na produkci bez debugu, vynutíme scénář ultra jako základní,
        // pokud v DB není nic nastaveno nebo pokud je tam výslovně standard a není to vynuceno v .env
        if (app()->isProduction() && ! config('app.debug') && ! config('performance.scenario')) {
             if (empty($settings['perf_scenario']) || $settings['perf_scenario'] === 'standard') {
                 $scenario = 'ultra';
             }
        }

        config([
            'performance.scenario' => $scenario,
            'performance.features.full_page_cache' => (bool) config('performance.features.full_page_cache', $settings['perf_full_page_cache'] ?? ($scenario === 'ultra')),
            'performance.features.fragment_cache' => (bool) config('performance.features.fragment_cache', $settings['perf_fragment_cache'] ?? in_array($scenario, ['aggressive', 'ultra'])),
            'performance.features.html_minification' => (bool) config('performance.features.html_minification', $settings['perf_html_minification'] ?? in_array($scenario, ['aggressive', 'ultra'])),
            'performance.features.livewire_navigate' => (bool) config('performance.features.livewire_navigate', $settings['perf_livewire_navigate'] ?? ($scenario === 'ultra')),
            'performance.features.lazy_load_images' => (bool) config('performance.features.lazy_load_images', $settings['perf_lazy_load_images'] ?? true),
        ]);

        // Pokud je vybrán scénář, přepíše jednotlivé features dle předdefinovaných šablon
        $this->applyScenarioDefaults($scenario);
    }

    public function getSettings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        // Na produkci používáme primárně redis, fallback na file.
        // Na localhostu preferujeme file před databází pro rychlost bootu.
        $defaultCache = config('cache.default');
        $store = 'file';

        if (app()->isProduction()) {
            $store = ($defaultCache === 'redis') ? 'redis' : 'file';
        } else {
            // Na localhostu použijeme 'file' i když je default 'database',
            // protože performance settings se čtou při každém bootu a nechceme zbytečný DB dotaz.
            $store = ($defaultCache === 'redis' || $defaultCache === 'array') ? $defaultCache : 'file';
        }

        try {
            // Pokusíme se o přístup k cache s krátkým timeoutem (pokud by ovladač podporoval)
            // nebo alespoň zachytíme RedisException hned při store() volání nebo remember()
            return $this->settings = Cache::store($store)->remember('performance_settings', 3600, function () {
                return $this->fetchSettingsFromDb();
            });
        } catch (\Throwable $e) {
            // Pokud cache selže (např. Redis Connection Refused), zkusíme to přes file, pokud to již nebyl file
            if ($store !== 'file') {
                try {
                    return $this->settings = Cache::store('file')->remember('performance_settings', 3600, function () {
                        return $this->fetchSettingsFromDb();
                    });
                } catch (\Throwable $e2) {
                    // Ignorujeme i tento fail a jdeme do DB
                }
            }

            // Pokud vše selže (např. lock timeout nebo DB nedostupná při fetch), načteme to přímo z DB bez cachování
            return $this->settings = $this->fetchSettingsFromDb();
        }
    }

    /**
     * Načte nastavení přímo z databáze.
     * Optimalizováno: Používá Query Builder pro bypass Eloquent overheadu a try-catch místo Schema::hasTable.
     */
    protected function fetchSettingsFromDb(): array
    {
        try {
            $settings = DB::table('settings')
                ->where('key', 'like', 'perf_%')
                ->get(['key', 'value'])
                ->pluck('value', 'key')
                ->toArray();

            // Normalizace lokalizovaných hodnot (JSON) na prosté řetězce
            foreach ($settings as $key => $value) {
                if (is_array($value)) {
                    $settings[$key] = reset($value);
                } elseif (is_string($value) && str_starts_with($value, '{') && str_ends_with($value, '}')) {
                    $decoded = json_decode($value, true);
                    if (is_array($decoded)) {
                        // Vezmeme první dostupnou hodnotu (např. 'cs')
                        $settings[$key] = reset($decoded);
                    }
                }
            }

            return $settings;
        } catch (\Throwable $e) {
            // Pokud tabulka neexistuje nebo je jiný problém, vrátíme prázdné pole
            return [];
        }
    }

    public function clearCache(): void
    {
        Cache::store('file')->forget('performance_settings');
        Cache::store('database')->forget('performance_settings');
        $this->settings = null;
    }

    protected function applyScenarioDefaults(string $scenario): void
    {
        if ($scenario === 'aggressive') {
            config([
                'performance.features.fragment_cache' => true,
                'performance.features.html_minification' => true,
            ]);
        } elseif ($scenario === 'ultra') {
            config([
                'performance.features.fragment_cache' => true,
                'performance.features.html_minification' => true,
                'performance.features.full_page_cache' => true,
                'performance.features.livewire_navigate' => true,
            ]);
        }
    }
}
