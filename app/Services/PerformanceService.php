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

        try {
            return $this->settings = Cache::store('database')->remember('performance_settings', 3600, function () {
                return $this->fetchSettingsFromDb();
            });
        } catch (\Throwable $e) {
            // Pokud cache selže (např. lock timeout), načteme to přímo z DB bez cachování v tomto requestu
            // Tím předejdeme QueryException, která by shodila celou aplikaci
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
            return DB::table('settings')
                ->where('key', 'like', 'perf_%')
                ->get(['key', 'value'])
                ->pluck('value', 'key')
                ->toArray();
        } catch (\Throwable $e) {
            // Pokud tabulka neexistuje nebo je jiný problém, vrátíme prázdné pole
            return [];
        }
    }

    public function clearCache(): void
    {
        Cache::forget('performance_settings');
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
