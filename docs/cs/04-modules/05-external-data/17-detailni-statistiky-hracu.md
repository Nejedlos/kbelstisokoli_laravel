# Detailní externí statistiky hráčů

Tento modul zajišťuje hloubkovou synchronizaci statistik hráčů z externího portálu [cz.basketball](https://cz.basketball).

## Účel
Umožnit hráčům i jejich spoluhráčům sledovat historický vývoj své výkonnosti, kariérní rekordy a detailní statistiky zápasů, které nejsou přímo v naší interní databázi (např. z působení v jiných klubech).

## Technický popis

### Databázové schéma
Protože produkční databáze zatím plně nepodporuje JSON operace pro komplexní výpočty, jsou data uložena ve strukturovaných tabulkách s explicitními sloupci:

1. **`external_player_stats`**: Obsahuje sezónní souhrny a kariérní průměry.
    - Sloupce pro všechny standardní metriky (GP, PPG, 2B, 3B, TH, Doskoky, Asistence, atd.).
    - Příznak `is_career_total` pro odlišení celoživotní statistiky.
2. **`external_player_matches`**: Obsahuje historii jednotlivých odehraných zápasů.
    - Ukládá datum, soupeře, body a další dostupné metriky pro každý zápas na ČBF.

### Synchronizace
Synchronizace probíhá v rámci `SyncPlayersJob`, který je možné spustit z administrace (stránka Debug operace).
- **Služba:** `PlayerSyncService`
- **Extraktor:** `PlayerDetailExtractor` (používá `Symfony/DomCrawler` pro parsování HTML).

Extraktor je navržen robustně, aby si poradil s různými formáty tabulek na ČBF (např. zlomky u trestných hodů `2.1/3.1` nebo průměry s čárkou).

## Způsob použití

### Pro uživatele (Hráče)
Hráči najdou své externí statistiky v sekci **Moje statistiky** pod záložkou "Osobní". Pod jejich aktuálními statistikami z našeho klubu se zobrazí sekce "Historie a kariéra" a "Poslední zápasy" z portálu cz.basketball.

### Pro spoluhráče
V sekci **Statistiky týmu** je možné kliknout na jméno kteréhokoliv spoluhráče. Tím se zobrazí jeho detailní profil, který nyní obsahuje i tyto externí historické údaje.

## Aktualizace dat
Data se aktualizují automaticky při spuštění hromadné synchronizace hráčů. Systém používá metodu `updateOrCreate`, takže existující záznamy jsou aktualizovány a nové přidány, čímž je zajištěna integrita historie.
