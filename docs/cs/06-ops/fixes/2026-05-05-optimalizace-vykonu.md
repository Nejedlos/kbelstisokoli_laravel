# Optimalizace výkonu a zrychlení TTFB (2026-05-05)

## Problém
Web vykazoval extrémně vysoký Time To First Byte (TTFB) na produkci, pohybující se kolem 7–9 sekund pro veřejné i členské stránky. Uživatel si stěžoval na nepoužitelnost webu i přes předchozí pokusy o cachování.

## Zjištění (Bottlenecky)
1. **Bootstrapping Laravelu:** Disk nebo CPU na sdíleném hostingu Webglobe byl přetížen čtením stovek konfiguračních a routovacích souborů při každém requestu. První spuštění `php artisan optimize` trvalo přes 20 sekund jen pro config a routy.
2. **Nefunkční Full-page Cache:** `FullPageCacheMiddleware` obsahoval příliš restriktivní logiku, která ignorovala requesty, pokud prohlížeč poslal session cookie (což Laravel dělá vždy). Tím byla cache pro 99 % hostů nepoužitelná.
3. **Vyloučené cesty:** Téměř všechny dynamické stránky (`/zapasy`, `/tymy`, `/`, atd.) byly v middleware natvrdo vyloučeny z cachování.
4. **Statistické výpočty:** Agregace statistik nad tisíci řádky v `TeamStatsService` a `PlayerStatsService` probíhaly při každém requestu/renderu Livewire bez trvalého cachování.

## Provedené změny

### 1. Optimalizace bootstrapu
- Na produkci byl vynucen příkaz `php artisan optimize`. TTFB díky tomu okamžitě klesl o několik sekund.
- Doporučeno spouštět `optimize` po každém nasazení (přidáno do Envoy scénářů).

### 2. Oprava a uvolnění Full-page Cache
- V `FullPageCacheMiddleware.php` byla odstraněna detekce guestů přes existenci session cookie. Nyní se spoléháme výhradně na `auth()->check()`.
- Z `excludedPaths` byly odebrány veřejné dynamické sekce (`/`, `zapasy*`, `tymy*`, atd.). Tyto stránky se nyní cachují pro hosty (hit TTFB ~0.3s).

### 3. Hloubkové cachování statistik
- Do `TeamStatsService` a `PlayerStatsService` bylo přidáno cachování výsledků náročných metod (`Cache::remember` na 24h).
- Cachovány jsou metody:
    - `getSeasonSummary`
    - `getAllPlayersStats`
    - `getMatchStats`
    - `getTeamLeaders`
    - `getTopScorers`
    - `getCareerOverview` (u hráčů)
- Klíče jsou prefixovány `team_stats_` a `player_stats_`.

### 4. Automatická invalidace
- `PerformanceObserver` byl rozšířen o mazání těchto nových cache klíčů. Při jakékoliv změně modelu (např. uložení výsledku zápasu, import statistik) se automaticky promažou statistiky v cache.

## Výsledky (Ověřeno přes curl)
- **Před optimalizací:** TTFB ~7.4s
- **Po optimalizaci (Miss):** TTFB ~0.4s
- **Po optimalizaci (Hit):** TTFB ~0.28s

Zrychlení je přibližně **25násobné**.
