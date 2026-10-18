# Orchestrace externí synchronizace (cz.basketball)

Tento dokument popisuje, jak probíhá automatizovaná synchronizace sportovních dat z webu `cz.basketball`.

## 1. Architektura

Synchronizace je rozdělena do dvou hlavních fází, aby byla šetrná k externímu serveru a efektivní:

### Omezený import zápasů a statistik

Konfigurace týmové sezóny může při `is_enabled = false` samostatně povolit
`metadata.matches_enabled = true`. Tento režim zahrnují `stats:import` i
`stats:sync-team-season <slug> active` (alias `active` vybere aktivní sezónu).
Přepínač `metadata.standings_enabled` zůstává nezávislý. Pro PBasket ponechte
`source_key = czbasketball` a použijte úplnou týmovou stránku se správným `y=`
jako URL seznamu zápasů; samostatná stránka `/zapasy` může mít omezený filtr.

- Importuje termíny, výsledky a individuální statistiky bez importu soupisky,
  vytváření či párování účtů podle jména, fotografií a finančních předpisů.
  Dříve spárovaní hráči zachovají vazbu; neznámí zůstanou ve statistice pod jménem
  a externím ID bez nového účtu. Součtový řádek se neukládá jako hráč.
- Přijímá pouze zápasy s externím ID, datem v dané sezóně a právě jedním výskytem
  konfigurovaného týmu. Neurčené termíny přeskočí. Nejednoznačná existující ID nebo
  konfliktní zápasy stejného dne zastaví import k prověření; režim neslučuje zápasy.
- Páruje existující zápasy přes `team_id` i `basketball_match_team`, zachovává jejich
  ID, vlastnictví a druh zápasu. Detail dostává ověřený kontext `sync_team_id`.
- Detaily plánuje pouze pro zápasy s termínem v minulosti. Čas načtení seznamu
  neblokuje detail: ten má samostatné `metadata.detail_synced_at`.
- Omezení se znovu vyhodnocují při zpracování detailu, včetně ručního volání.
  Plné `is_enabled = true` zachovává původní rozsah včetně soupisky.

Boxscore se nahrazuje pouze při přítomnosti neprázdné hráčské tabulky vlastního
týmu s číselnými body. Chybějící či pouze soupeřova tabulka nesmaže dřívější
statistiky ani nepřidá úspěšný `boxscore_synced_at`. Náhrada je jedna transakce
omezená na daný tým a zápas; zámek sezóny chrání stávající přepočet sezónních
souhrnů před souběhem. Po importu se ruší pouze známé cache statistik dotčeného
týmu a sezóny. `--force` tato omezení neobchází.

1.  **Fáze 1: Sezónní přehled (`SyncTeamSeasonJob`)**
    - Stáhne soupisku týmu.
    - Stáhne seznam zápasů (termíny, soupeři, výsledky).
    - Vytvoří/aktualizuje záznamy v tabulkách `users` (ghost profily), `matches` a `opponents`.
    - Naplánuje Fázi 2 pro nové nebo změněné zápasy.

2.  **Fáze 2: Detail zápasu (`SyncMatchDetailJob`)**
    - Stáhne boxscore konkrétního zápasu.
    - Uloží individuální statistiky hráčů do `statistic_rows`.
    - Přepočítá sezónní agregace hráče a týmu.

## 2. Klíčové komponenty

### `App\Services\Stats\Sync\ExternalStatsSyncService`
Hlavní orchestrátor, který volá extraktory a specializované synchronizační služby.

### `App\Jobs\Stats\SyncTeamSeasonJob`
- **Vstup:** `team_id`, `season_id`, `options`.
- **Frekvence:** Doporučeno 1x denně nebo při ručním spuštění.
- **Limit:** Omezuje počet detailních synchronizací zápasů (výchozí 15), aby se předešlo zahlcení.

### `App\Jobs\Stats\SyncMatchDetailJob`
- **Vstup:** `match_id`, `options`.
- **Vlastnosti:** Podporuje retries (3x) a timeout (60s).

## 3. Idempotence a šetrnost

- **Fragment Hashing:** Systém si ukládá SHA256 hash HTML fragmentu (tabulky). Pokud se hash nezměnil oproti poslednímu úspěšnému běhu, parsování se přeskočí.
- **Auditování:** Každý běh je logován v `external_import_runs`.
- **Dávkování:** Detailní statistiky se stahují postupně přes frontu.

## 4. Fallback na AI

Pokud `DOM/XPath` extractor selže (např. při změně HTML struktury webu), systém se automaticky pokusí o parsování pomocí LLM (OpenAI). Informace o použití fallbacku je uložena v metadatech běhu.

## 5. Ruční spuštění a debugování

### Spuštění přes Tinker
```php
// Kompletní sync pro tým ID 1 v sezóně ID 5
app(\App\Services\Stats\Sync\ExternalStatsSyncService::class)->syncTeamSeason(1, 5);

// Vynucení resyncu všech zápasů
app(\App\Services\Stats\Sync\ExternalStatsSyncService::class)->syncTeamSeason(1, 5, ['force' => true, 'limit' => 50]);
```

### Debugování
Chyby a průběh lze sledovat v:
1.  Tabulce `external_import_runs` (pole `status`, `error_summary`, `metadata`).
2.  Logu aplikace (`storage/logs/laravel.log`).
3.  Snapshoty staženého HTML v `storage/app/external/czbasketball/`.
