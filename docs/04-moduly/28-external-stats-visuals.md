# Vizualizace externích statistik

Tento dokument popisuje způsob zobrazení a výpočtu statistik získaných z externích zdrojů (cz.basketball) na frontendu a v členské zóně.

## 1. Datová vrstva (Services)

Pro čtení statistik z databáze slouží dvě hlavní služby:

### PlayerStatsService
- `getSeasonSummary($userId, $seasonId, $teamId)`: Vrací souhrnné sezónní statistiky hráče. Pokud nejsou předpočítané, provede fallback výpočet z jednotlivých zápasů.
- `getPerGameSeries($userId, $seasonId, $teamId)`: Vrací časovou řadu statistik pro grafy (zápas po zápase).

### TeamStatsService
- `getSeasonSummary($teamId, $seasonId)`: Vrací souhrn týmu (GP, W/L, body).
- `getTopScorers($teamId, $seasonId)`: Vrací seznam nejlepších střelců týmu.
- `getWinLossBalance($teamId, $seasonId)`: Vypočítává bilanci výher a proher z odehraných zápasů.

## 2. Komponenty uživatelského rozhraní

### Členská zóna: Moje statistiky (`MyStatistics`)
Zobrazuje osobní výkon hráče v sezóně.
- **Karty souhrnu:** Zápasy, Body, PPG, Minuty, Procentuální úspěšnost střelby (2B, 3B, TH).
- **Grafy (ApexCharts):**
    - Vývoj bodů v sezóně (Line chart).
    - Úspěšnost střelby (Bar chart).
- **Zápis o zápasech:** Detailní tabulka všech odehraných zápasů s individuálními statistikami.

### Veřejná část: Týmová sezóna (`TeamSeasonStats`)
Zobrazuje celkový výkon vybraného týmu.
- **Klíčové metriky:** Bilance výher/proher, průměr vstřelených a obdržených bodů.
- **Top střelci:** Tabulka hráčů s nejvyšším bodovým průměrem a počtem odehraných zápasů.
- **Graf výkonu:** Vizualizace trendu skórování týmu v čase.

## 3. Výpočet metrik

Všechny metriky jsou počítány z dat uložených v tabulce `statistic_rows` (sety `match-boxscore-external`, `player-season-summary-external`, `team-season-summary-external`).

| Metrika | Výpočet / Zdroj | Poznámka |
| :--- | :--- | :--- |
| **PPG (Points Per Game)** | `total_points / games_played` | |
| **Shooting %** | `(made / attempted) * 100` | Zobrazuje se pouze, pokud jsou dostupné pokusy (attempts). |
| **W/L Balance** | Porovnání `score_home` vs `score_away` | Bere v úvahu, zda je tým v zápase veden jako `is_home`. |
| **VAL (Efficiency)** | Přímý import z externího boxscoru | Souhrnný index užitečnosti hráče. |

## 4. Integrace grafů

Komponenty jsou připraveny jako "skeletony" pro knihovnu **ApexCharts**. 
- Data jsou připravena v polích `$perGameSeries` a `$summary`.
- Inicializace grafů probíhá na frontendu pomocí JavaScriptu navázaného na Livewire eventy.

## 5. Přístupnost a filtry
Hráči mohou v členské zóně přepínat mezi sezónami a týmy, ve kterých figurovali na soupisce. Veřejná část umožňuje prohlížet statistiky všech týmů klubu, které mají v dané sezóně aktivní externí mapování.
