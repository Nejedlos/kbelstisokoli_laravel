# Debug & Operations Panel

Tento modul slouží jako centrální dashboard pro správu a monitorování všech procesů souvisejících s externími statistikami a importy. Je určen výhradně pro administrátory systému.

## 1. Přehled funkcí
Dashboard je rozdělen do několika klíčových sekcí:

### A. Health Status (Stav systému)
Semafor zobrazující aktuální stav kritických komponent:
- **Database:** Ověření spojení s databází.
- **Queue (Jobs):** Počet čekajících úloh ve frontě (varování při vysokém počtu).
- **Scheduler:** Kontrola, zda běží plánovač úloh (pomocí heartbeat mechanismu).
- **Storage:** Ověření práv k zápisu do lokálního úložiště.
- **External Fetcher:** Dostupnost externího zdroje `cz.basketball`.

### B. Externí Statistiky (Aktivní sezóna)
Detailní přehled pro každý sledovaný tým (např. Muži C, Muži E):
- Počet synchronizovaných zápasů v aktuální sezóně.
- Počet naimportovaných řádků statistik (boxscore).
- Počet nespárovaných (unmatched) hráčů, kteří vyžadují pozornost admina.
- Čas poslední úspěšné synchronizace a případná poslední chybová zpráva.
- **Akce:** Tlačítko pro okamžité spuštění synchronizace týmu.

### C. Legacy Import
Sledování průběhu poslední nahrané dávky historických statistik:
- Procentuální progress bar zpracování souborů.
- Počty úspěšných a selhaných souborů.
- Rychlý odkaz na detail dávky.

### D. Audit Log
Tabulka posledních 20 běhů synchronizace (`ExternalImportRun`) s barevným odlišením stavů (Success, Failed, Partial Failed, Skipped) a počty naimportovaných záznamů.

## 2. Rychlé akce (Header Actions)
V horní části panelu jsou k dispozici globální akce:
- **Sync All Active:** Zařadí do fronty synchronizaci všech týmů pro aktivní sezónu.
- **Recompute Stats:** Přepočítá sezónní souhrny (agregace) hráčů a týmu pro aktivní sezónu.

## 3. Diagnostika chyb
Pokud některá komponenta svítí červeně:
1. **Database FAIL:** Zkontrolujte `.env` soubor a dostupnost DB serveru.
2. **Scheduler FAIL:** Prověřte, zda na serveru běží cron úloha `* * * * * php artisan schedule:run`.
3. **Queue (vysoký počet):** Zkontrolujte, zda běží worker `php artisan queue:work`.
4. **Fetcher FAIL:** Externí web může být dočasně nedostupný nebo blokuje požadavky.

## 4. Technické detaily
- **Heartbeat:** Plánovač každou minutu ukládá timestamp do cache (`scheduler_heartbeat`).
- **Cesta:** `/admin/debug-operations`
- **Třída:** `App\Filament\Pages\DebugOperations`
- **Šablona:** `resources/views/filament/pages/debug-operations.blade.php`
