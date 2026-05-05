# Oprava scheduleru a odstranění duplicitních úloh (2026-05-05)

## Problém
1. **Chyba 126:** V produkčním prostředí docházelo k chybě `Scheduled command queue:work --stop-when-empty failed with exit code [126]`. To bylo způsobeno pokusem Laravelu spustit externí binárku `/usr/bin/php`, k čemuž na sdíleném hostingu Webglobe nemá scheduler (spouštěný přes HTTP) dostatečná oprávnění nebo není cesta k PHP správně definována pro subprocesy.
2. **Duplicity:** V `routes/console.php` byly definovány desítky plánovaných úloh, které se zároveň spouštěly dynamicky z databáze (tabulka `cron_tasks`). To vedlo k dvojímu spouštění náročných importů statistik, což zbytečně vytěžovalo server a zpomalovalo aplikaci.

## Řešení
1. **Změna mechanismu volání:** Příkaz `queue:work` v `routes/console.php` byl změněn z `Schedule::command()` na `Schedule::call(fn() => Artisan::call('queue:work', ...))`. Tento přístup spouští příkaz v rámci stávajícího PHP procesu, nevyžaduje externí binárku a je plně kompatibilní s prostředím Webglobe.
2. **Konsolidace úloh:** Z `routes/console.php` byly odstraněny všechny úlohy, které jsou již spravovány přes administraci v tabulce `cron_tasks`. V souboru zůstaly pouze nízkoúrovňové systémové úlohy (priming cache, údržba fronty, generování sitemap), které v databázi nejsou.
3. **Pojmenování úloh:** U systémových úloh bylo přidáno explicitní pojmenování (`->name()`), což je vyžadováno pro správnou funkci `onOneServer()` a zabraňuje kolizím.

## Dopad
- Eliminace periodických chybových hlášení v logu a e-mailových reportech.
- Snížení zátěže serveru odstraněním duplicitních úloh.
- Zlepšení stability synchronizace dat díky funkčnímu odbavování fronty.

## Verifikace
- `php artisan schedule:list` na produkci potvrzuje čistý seznam úloh bez duplicit.
- Scheduler endpoint vrací korektní výstup bez výjimek.
