# Optimalizace výkonu a cachování (Performance & Cache)

Tento dokument popisuje systém pro řízení výkonu aplikace Kbelští sokoli, který umožňuje dynamicky přepínat mezi různými úrovněmi optimalizace.

## 1. Výkonnostní scénáře

Aplikace podporuje tři základní scénáře, které lze nastavit v administraci (nebo v tabulce `settings` pod klíčem `perf_scenario`).

| Scénář | Popis | Vhodné pro |
| :--- | :--- | :--- |
| **Standard** | Žádné pokročilé cachování, čistý výstup. | Vývoj, ladění chyb. |
| **Aggressive** | Aktivní fragment cache, minifikace HTML, `wire:navigate`. | Produkční provoz (výchozí). |
| **Ultra** | Full-page cache (pro hosty), extrémní minifikace, agresivní lazy-loading. | Špičky, statické weby. |

## 2. Klíčové technologie

### 2.1 Fragment Caching (`@cacheFragment`)
V Blade šablonách používáme vlastní direktivu pro cachování náročných bloků (např. menu, widgety se zápasy). 
- **Použití:** `@cacheFragment('sidebar_matches', 3600)` ... `@endCacheFragment`
- **Invalidace:** Cache se automaticky maže při uložení libovolného modelu přes `PerformanceObserver`.

### 2.2 SPA Navigace (`wire:navigate`)
Pro pocit bleskové odezvy při přechodu mezi stránkami využíváme Livewire navigaci. 
- Tato funkce je aktivní pouze v scénářích `aggressive` a `ultra`.
- Zajišťuje, že se při navigaci nepřekresluje celé okno prohlížeče, ale pouze obsah.

### 2.3 Globální View Data Caching
V `AppServiceProvider` jsou data pro globální prvky (Branding, Menu, Oznámení) cachována pomocí `Cache::remember`. To výrazně snižuje počet SQL dotazů na každém requestu.

## 3. Monitoring a testování

Aplikace obsahuje `PerformanceTestService`, který umožňuje měřit metriky pro jednotlivé scénáře.

- **Měřené hodnoty:**
    - `duration_ms`: Celková doba trvání requestu.
    - `query_count`: Počet SQL dotazů.
    - `memory_mb`: Spotřeba paměti.
- **Spuštění testu:** `php artisan performance:check` (pokud je příkaz dostupný) nebo přes administraci v sekci Výkon.

## 4. Správa přes CLI

- **Změna scénáře:** `php artisan db:seed --class=PerformanceSettingsSeeder` (nastaví doporučený `aggressive` scénář).
- **Vymazání veškeré cache:** `php artisan cache:clear`
