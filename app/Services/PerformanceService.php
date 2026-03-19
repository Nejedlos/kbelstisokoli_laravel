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
        $scenario = $settings['perf_scenario'] ?? 'standard';

        // Povolení vynucení scénáře pro admina (užitečné pro testy)
        // Optimalizováno: Kontrola auth pouze pokud jsme v HTTP requestu a auth je k dispozici
        if (! app()->runningInConsole() && request()->has('perf_scenario')) {
            try {
                if (auth()->check() && auth()->user()->can('access_admin')) {
                    $scenario = request('perf_scenario');
                }
            } catch (\Throwable $e) {
                // Tichý fail pokud auth ještě není připraven (např. v rané fázi bootu)
            }
        }

        config([
            'performance.scenario' => $scenario,
            'performance.features.full_page_cache' => (bool) ($settings['perf_full_page_cache'] ?? false),
            'performance.features.fragment_cache' => (bool) ($settings['perf_fragment_cache'] ?? false),
            'performance.features.html_minification' => (bool) ($settings['perf_html_minification'] ?? false),
            'performance.features.livewire_navigate' => (bool) ($settings['perf_livewire_navigate'] ?? false),
            'performance.features.lazy_load_images' => (bool) ($settings['perf_lazy_load_images'] ?? true),
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
