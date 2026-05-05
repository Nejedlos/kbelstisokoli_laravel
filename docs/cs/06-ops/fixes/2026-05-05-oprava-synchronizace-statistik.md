# Oprava synchronizace statistik a fronty úloh (2026-05-05)

## Problém
Na produkci byly prázdné statistiky hráčů v sekci zápasů, přestože synchronizace aktivní sezóny v cronu běžela.

## Zjištění
1. **Historické sezóny:** Cron úlohy se zaměřovaly primárně na aktivní sezónu. Historické sezóny nebyly plně sesynchronizovány.
2. **Fronta úloh (Queue):** Synchronizace detailů zápasů (boxscore) se vždy odesílá do fronty (`SyncMatchDetailJob`). Na produkčním hostingu Webglobe však neběžel žádný proces (queue worker), který by tuto frontu zpracovával. Úlohy se hromadily v databázi (přes 700 čekajících úloh).
3. **Konfigurace:** Driver fronty je nastaven na `database`.

## Provedené změny
1. **Manuální synchronizace:** Spuštěna plná synchronizace všech týmů a všech sezón (38 kombinací) s vynucením (`--force`) a hloubkovým stažením detailů (`--excesive`):
   `php8.4 artisan stats:sync-team-season all all --force --excesive --no-interaction`
2. **Zpracování fronty:** Manuálně spuštěn worker pro vyčištění nahromaděných úloh:
   `php8.4 artisan queue:work --stop-when-empty`
3. **Automatizace (Fix):** Do `routes/console.php` bylo přidáno pravidelné zpracování fronty každých 10 minut:
   `Schedule::command('queue:work --stop-when-empty')->everyTenMinutes()->onOneServer();`
   Tím je zajištěno, že se úlohy z cronu (který běží pod HTTP requestem) budou postupně odbavovat.

## Ověření bezpečnosti
Prověřena logika v `MatchSyncService`, `RosterSyncService` a `ExternalStatsSyncService`. 
- Systém používá `updateOrCreate` principy.
- Před importem nových statistik zápasu dochází k vyčištění starých statistik pro daný zápas (`clearMatchBoxscore`), což brání duplikacím.
- Existuje robustní logika pro slučování duplicitních zápasů podle `external_id` nebo identity klíče (datum, soupeř, is_home).
- **Závěr:** Synchronizace je bezpečná a neničí existující data.
