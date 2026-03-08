# CLI Příkazy (Artisan) - Externí statistiky

Tento dokument popisuje sadu Artisan příkazů pro manuální správu a synchronizaci externích statistik z příkazové řádky.

## 1. Synchronizace týmu a sezóny
Tento příkaz spustí kompletní synchronizaci soupisky a seznamu zápasů pro daný tým a sezónu.

```bash
php artisan stats:sync-team-season {teamSlug} {seasonNameOrId} [options]
```

### Argumenty:
- `teamSlug`: Slug týmu (např. `muzi-c`, `muzi-e`).
- `seasonNameOrId`: Název sezóny (např. `2024/2025`) nebo její interní ID.

### Volby (Options):
- `--dry-run`: Spustí pouze parsování a zobrazí náhled (roster, matches), ale nic nezapisuje do databáze.
- `--force`: Ignoruje hash obsahu a vynutí stažení a zpracování i v případě, že se na webu nic nezměnilo.
- `--max-matches=20`: Limituje počet detailů zápasů, které se mají stáhnout (aby nedošlo k přetížení).
- `--recent-days=3`: Pokud je nastaveno, prioritně se synchronizují zápasy z posledních X dní.
- `--sync`: Spustí synchronizaci přímo v tomto procesu (bez použití fronty).

**Příklad:**
```bash
php artisan stats:sync-team-season muzi-c 2024/2025 --sync
```

---

## 2. Synchronizace detailu zápasu
Stáhne a zpracuje detailní boxscore (statistiky hráčů) pro konkrétní zápas.

```bash
php artisan stats:sync-match {matchExternalId} {seasonNameOrId} {teamSlug} [options]
```

### Argumenty:
- `matchExternalId`: Externí ID zápasu z URL na cz.basketball (např. `519196`).
- `seasonNameOrId`: Název nebo ID sezóny.
- `teamSlug`: Slug týmu.

**Příklad:**
```bash
php artisan stats:sync-match 519196 2024/2025 muzi-c --force
```

---

## 3. Přepočet statistik
Pokud dojde k manuálnímu párování hráčů nebo opravě dat, je nutné přepočítat sezónní souhrny (summary).

```bash
php artisan stats:recompute {teamSlug} {seasonNameOrId}
```

Tento příkaz přepočítá:
1. `PLAYER_SEASON_SUMMARY`: Součty a průměry pro všechny hráče v dané sezóně.
2. `TEAM_SEASON_SUMMARY`: Celkové statistiky týmu.

---

## 4. Import historické dávky (Legacy)
Spustí zpracování dříve nahrané dávky HTML souborů.

```bash
php artisan legacy:import-batch {batchId} [--sync]
```

---

## 5. Diagnostika systému (Health Check)
Rychle ověří, zda jsou všechny komponenty systému (databáze, fronty, storage, externí web) dostupné a funkční.

```bash
php artisan stats:health
```

Výstup obsahuje:
- Stav infrastruktury (Database, Queue, Scheduler, Storage, External Fetcher).
- Přehled synchronizace pro aktivní sezónu (poslední běhy, počty zápasů, nespárovaní hráči).

---

## 6. Úklid starých dat
Promaže staré HTML snapshoty a historii běhů (import runs).

```bash
php artisan external-stats:cleanup {--days=30} {--runs-months=3}
```

---

## Troubleshooting a logování
- Všechny příkazy logují své běhy do tabulky `external_import_runs`.
- Pokud příkaz selže, zkontrolujte `error_summary` v tabulce běhů nebo standardní Laravel logy.
- Snapshoty HTML souborů (při chybě nebo AI fallbacku) najdete v `storage/app/external/czbasketball/`.
