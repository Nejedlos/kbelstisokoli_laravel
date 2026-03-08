# QA Master Plan – Excesivní testy "No Surprises"

Tento dokument definuje rozsah a metodiku testování systému externích statistik, legacy importů a uživatelského rozhraní.

## 1. Systémové zdraví (System Health)
- **QA-SYS-01: DB Konektivita** – Ověření spojení s databází.
- **QA-SYS-02: Migrace** – Kontrola, zda jsou všechny migrace aplikovány.
- **QA-SYS-03: Queue Driver** – Ověření konfigurace front (sync/database/redis).
- **QA-SYS-04: Storage** – Test zápisu a čtení v `storage/app`.
- **QA-SYS-05: Legacy Source** – Ověření existence a čitelnosti `storage/app/legacystats`.

## 2. Autentizace a Oprávnění (Auth & Roles)
- **QA-AUTH-01: Guest Access** – Host nemá přístup do `/admin` ani `/member`.
- **QA-AUTH-02: Member Access** – Člen má přístup do `/member`, ale ne do `/admin`.
- **QA-AUTH-03: Admin Access** – Admin má přístup do `/admin` i `/member`.
- **QA-AUTH-04: Redirects** – Ověření správného přesměrování po přihlášení dle role.

## 3. Externí Sync Pipeline (CzBasketball)
- **QA-SYNC-01: Roster Extraction** – Parsování soupisky z HTML fixture.
- **QA-SYNC-02: Matches List Extraction** – Parsování seznamu zápasů z HTML fixture.
- **QA-SYNC-03: Match Detail Extraction** – Parsování boxscoru z HTML fixture.
- **QA-SYNC-04: Upsert Logic** – Ověření vytvoření/aktualizace zápasů a soupeřů.
- **QA-SYNC-05: Statistics Persistence** – Ukládání řádků statistik do `statistic_rows`.
- **QA-SYNC-06: Idempotence** – Opakovaný sync stejných dat neprodukuje duplicity.
- **QA-SYNC-07: Aggregations** – Přepočet sezónních souhrnů (Player/Team summary).

## 4. Legacy Import Pipeline (Local Files)
- **QA-LEG-01: File Discovery** – Nalezení HTML souborů v `storage/app/legacystats`.
- **QA-LEG-02: Classification** – Detekce sezóny a typu souboru (hráči/tým/tabulka).
- **QA-LEG-03: DOM Parsing** – Extrakce tabulek z historických HTML souborů.
- **QA-LEG-04: Canonical Mapping** – Správné mapování sloupců na interní klíče (pts, gp, ...).
- **QA-LEG-05: Persistence** – Ukládání dat do legacy statistických sad.
- **QA-LEG-06: Idempotence** – SHA256 hash zabraňuje duplicitám.

## 5. Uživatelské rozhraní (UI & UX)
- **QA-UI-01: Admin Pages** – Renderování Debug panelu, Import runs a Legacy importu.
- **QA-UI-02: Member Dashboard** – Zobrazení "Moje statistiky" pro přihlášeného hráče.
- **QA-UI-03: Public Team Stats** – Veřejný přehled sezóny týmu.
- **QA-UI-04: Graphs & Charts** – Ověření JSON endpointů pro ApexCharts.
- **QA-UI-05: Empty States** – Správné zobrazení, když nejsou k dispozici žádná data.

## 6. Brutální Smoke Run
- **QA-SMOKE-01: End-to-End** – Kompletní proces od resetu DB po finální ověření všech entit.

## Metodika spouštění
1. `php artisan qa:preflight` – Rychlá kontrola prostředí.
2. `php artisan test` – Spuštění automatizovaných testů.
3. `php artisan qa:run --full` – Celkový integrační test se zápisem do DB.
