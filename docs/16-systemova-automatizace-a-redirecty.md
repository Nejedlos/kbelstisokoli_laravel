# Systémová automatizace a Redirecty

Účel: Správa plánovaných úloh (Scheduler), automatizace workflow a správa přesměrování (Redirect Manager).

## 1. Scheduler a Cron (Automatizace)
Úlohy jsou definovány v databázi a dynamicky registrovány do Laravel scheduleru.

### Architektura
- **Dynamický Scheduler:** Úlohy jsou v tabulce `cron_tasks`.
- **Job Wrapper:** Příkazy jsou spouštěny přes `RunCronTaskJob` s detailním logováním do `cron_logs`.
- **Webový trigger:** Pro prostředí bez systémového cronu je připravena URL `/system/cron/run?token=...`.

### Implementované úlohy
1. **RSVP Upomínky:** Upomínky pro nepotvrzené akce.
2. **Sync oznámení:** Automatická deaktivace prošlých bannerů.
3. **Import statistik:** Pipeline pro stahování dat.
4. **Systémový úklid:** Promazávání starých logů.

## 2. Redirect manager a legacy migrace
Účel: Správa přesměrování (301/302) ze starého webu na nový.

### Datový model
- `source_path`: Původní cesta.
- `target_type`: `internal` nebo `external`.
- `status_code`: 301 nebo 302.
- `match_type`: `exact` nebo `prefix`.

### Redirect Resolution
- Logika je v `RedirectMiddleware` (web skupina).
- Vyhodnocuje se před standardním routováním nebo jako fallback před 404.
- Obsahuje anti-loop ochranu.

### Chybové stránky (Error UX)
Vytvořeny moderní sportovní šablony v `resources/views/errors/`:
- `404.blade.php`: Stránka nenalezena.
- `403.blade.php`: Přístup odepřen (sportovní paralela s faulem).
- `410.blade.php`: Obsah trvale odstraněn.
