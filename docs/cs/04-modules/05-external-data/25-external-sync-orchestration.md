# Orchestrace externí synchronizace (cz.basketball)

Tento dokument popisuje, jak probíhá automatizovaná synchronizace sportovních dat z webu `cz.basketball`.

## 1. Architektura

Synchronizace je rozdělena do dvou hlavních fází, aby byla šetrná k externímu serveru a efektivní:

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
