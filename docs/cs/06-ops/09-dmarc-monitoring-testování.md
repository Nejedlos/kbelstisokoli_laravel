# Testování DMARC Monitoringu

Tento dokument popisuje, jak otestovat celou pipeline DMARC monitoringu, od přijetí reportu až po odeslání varovného e-mailu.

## 1. Příprava
Před testováním se ujistěte, že:
- V souboru `.env` (na produkci) je nastaven technický e-mail: `ERROR_REPORT_EMAIL=nejedlymi@gmail.com`.
- Plánovač úloh (cron) běží (každou minutu volá `/system/schedule/{token}`).
- Mailbox `dmarc@kbelstisokoli.cz` je aktivní a má správné přihlašovací údaje (v administraci v sekci DMARC Mailboxy).

## 2. Spuštění testu

### Krok A: Odeslání testovacího reportu
Spusťte Artisan příkaz, který vygeneruje "fake" kritický report od Google a pošle jej na monitorovací schránku:
```bash
php artisan dmarc:test-send
```
Tento příkaz pošle e-mail s GZIP přílohou, která obsahuje XML report simulující kritické selhání SPF i DKIM (např. z IP 1.2.3.4).

### Krok B: Zpracování reportu
Plánovač automaticky spouští stahování reportů každou hodinu. Pro okamžitý výsledek můžete příkaz spustit ručně:
```bash
php artisan dmarc:ingest
```
V konzoli byste měli vidět informaci o zpracování mailboxu a nalezení testovacího reportu.

### Krok C: Ověření notifikace
Pokud systém vyhodnotí report jako kritický (což testovací report dělá), provede následující:
1. Vytvoří záznam v tabulce `dmarc_incidents`.
2. Odešle e-mail na adresu nastavenou v `ERROR_REPORT_EMAIL`.
3. Zkontrolujte schránku `nejedlymi@gmail.com` (včetně spamu).

## 3. Kontrola v administraci
Všechny výsledky jsou vidět v administraci Filament:
- **DMARC Reports:** Seznam všech přijatých a stažených XML reportů.
- **DMARC Records:** Detailní řádky z reportů (IP adresy, počty e-mailů, stavy SPF/DKIM).
- **DMARC Incidents:** Přehled otevřených problémů, které vyžadují pozornost technika.

## 4. Debugování Heartbeatu
Pro ověření, že plánovač (scheduler) na pozadí skutečně běží:
- Sledujte soubor `storage/logs/laravel.log`.
- Každou minutu by se v něm měl objevit řádek: `[timestamp] production.INFO: Scheduler Heartbeat tick.`
- Pokud se řádek neobjevuje, cron na hostingu není správně nastaven.
