# Detailní popis migračních seederů

Tento dokument poskytuje technický popis a logiku všech seederů použitých pro migraci dat z původního systému (kbelstisokoli_old) do nového systému založeného na Laravel 12 a Filament 5.

## Přehled seederů a doporučené pořadí spuštění

Z důvodu datových závislostí (cizí klíče) je nutné dodržet následující pořadí:

1. `LegacyUserMigrationSeeder` – základní uživatelská data (včetně adres).
2. `SeasonMigrationSeeder` – identifikace a sjednocení sezón.
3. `EventMigrationSeeder` – zápasy, tréninky a klubové akce.
4. `AttendanceMigrationSeeder` – docházka, RSVP a metadata o zápisu.
5. `FinanceMigrationSeeder` – tarify, konfigurace, platby a pokuty (včetně šablon).
6. `TrophyMigrationSeeder` – migrace klubových trofejí a úspěchů.

---

## 1. LegacyUserMigrationSeeder
**Účel:** Přenos uživatelské základny a hráčských profilů.

- **Zdrojové tabulky:** `roster`
- **Cílové tabulky:** `users`, `player_profiles`, `team_user`
- **Klíčová logika:**
    - Mapování polí ze staré DB (např. `jmeno`, `prijmeni`, `email`, `adresa` -> `address_street`).
    - Převod pozic hráčů (např. "rozehrávač" -> `BasketballPosition::POINT_GUARD`).
    - Generování výchozích hesel (pokud nebyla v původní DB, je vygenerováno náhodné).
    - Ukládání původního ID (`id` z tabulky `roster`) do `metadata->legacy_r_id` v tabulce `users`.
    - Přiřazení do týmů na základě sloupce `team` ve staré DB.

## 2. SeasonMigrationSeeder & SeasonUnifySeeder
**Účel:** Vytvoření číselníku sezón a zajištění konzistence formátu.

- **Zdrojové tabulky:** `zapasy`, `dochazka`, `web_realna_dochazka`, `web_platici`
- **Cílové tabulky:** `seasons`
- **Klíčová logika:**
    - `SeasonMigrationSeeder` prochází všechny tabulky, kde se vyskytuje sloupec `sezona`, a sbírá unikátní hodnoty.
    - **Sjednocení formátu:** Všechny sezóny jsou převedeny na formát `YYYY/YYYY` (např. `2024-2025` se mění na `2024/2025`).
    - `SeasonUnifySeeder` zajišťuje, že v nové databázi neexistují duplicity a že všechny existující vazby (v `matches`, `user_season_configs` atd.) ukazují na sjednocený záznam.
    - Nejnovější sezóna je automaticky nastavena jako `is_active = true`.

## 3. EventMigrationSeeder
**Účel:** Přesun historických událostí (zápasy, tréninky, akce).

- **Zdrojové tabulky:** `zapasy`
- **Cílové tabulky:** `matches`, `trainings`, `club_events`
- **Klíčová logika:**
    - Rozdělení záznamů podle sloupce `druh`:
        - `MI, PO, TUR, PRATEL` -> Zápasy (`BasketballMatch`).
        - `TR` -> Tréninky (`Training`).
        - `ALL` -> Klubové akce (`ClubEvent`).
    - **Automatické odvození sezóny:** Pokud u záznamu chybí název sezóny, odvodí se podle data (přelom je 1. září).
    - **Zpracování výsledků:** Rozparsování řetězce "85:72" do samostatných sloupců `score_home` a `score_away`.
    - **Mapování soupeřů:** Automatické vytváření záznamů v tabulce `opponents` podle názvu.
    - **Metadata:** Ukládání původního ID (`id` z tabulky `zapasy`) do `metadata->legacy_z_id` pro prevenci duplicit při opakovaném spuštění.

## 4. AttendanceMigrationSeeder
**Účel:** Přenos historie docházky a RSVP odpovědí.

- **Zdrojové tabulky:** `web_zapasy_rsvp`, `web_realna_dochazka`, `dochazka`
- **Cílové tabulky:** `attendances`
- **Klíčová logika:**
    - Propojení uživatelů přes `legacy_r_id` a událostí přes `legacy_z_id`.
    - Migrace RSVP (předpokládaná účast): `ano` -> `attending`, `ne` -> `not_attending`, `nevim` -> `maybe`.
    - Migrace reálné docházky: Mapování statusů (přítomen, omluven, neomluven).
    - **Metadata:** Ukládání času zápisu docházky (`web_realna_dochazka.datum`) do `metadata->legacy_recorded_at`.
    - Zvláštní ošetření pro různé formáty zápisů ve staré DB (např. `dochazka` vs `web_realna_dochazka`).

## 5. FinanceMigrationSeeder
**Účel:** Kompletní přenos finanční historie a aktuálních nastavení.

- **Zdrojové tabulky:** `web_vypocty_platby`, `web_platici`, `web_platby`, `web_pokuty`, `web_vypocty_pokuty`
- **Cílové tabulky:** `financial_tariffs`, `fine_templates`, `user_season_configs`, `finance_charges`, `finance_payments`, `charge_payment_allocations`
- **Klíčová logika:**
    - **Tarify:** Migrace výpočtových schémat do `financial_tariffs`.
    - **Šablony pokut:** Migrace sazebníku pokut do `fine_templates` (přes `FineTemplateSeeder`).
    - **Konfigurace:** Přenos individuálních nastavení členů (od kdy uctovat, výjimky, počáteční zůstatky) do `user_season_configs`.
    - **Předpisy (Charges):**
        - Automatické generování předpisů pro členské příspěvky na základě konfigurací uživatelů.
        - Migrace pokut jako samostatných předpisů typu `fine`, s volitelnou vazbou na `fine_templates`.
    - **Platby:** Migrace skutečných plateb (banka/hotovost).
    - **Automatické párování (Allocation):** Skript prochází všechny platby uživatele a podle FIFO principu (od nejstaršího po splatnosti) je přiřazuje k neuhrazeným předpisům. Tím se automaticky dopočítá aktuální dluh/přeplatek v novém systému.

## 6. TrophyMigrationSeeder
**Účel:** Přenos historických klubových úspěchů a trofejí.

- **Zdrojové tabulky:** `web_trophy`
- **Cílové tabulky:** `club_competitions`, `club_competition_entries`
- **Klíčová logika:**
    - Vytvoření soutěží v `ClubCompetition` (např. "Střelec sezóny").
    - Odhad sezóny na základě data vyhlášení.
    - Mapování oceněných (1.-3. místo) do `ClubCompetitionEntry`.
    - Párování osob na uživatele podle jména, nebo uložení jako textový label.

---

## Technické poznámky
- **Idempotence:** Většina seederů používá `updateOrCreate` nebo kontrolu přes `metadata`, což umožňuje jejich opakované spuštění bez rizika duplicit.
- **Transakce:** Kritické části (zejména párování plateb) jsou obaleny v DB transakcích.
- **Lokalizace:** U `ClubEvent` jsou textová pole (title, description) ukládána jako JSON objekty pro podporu `Spatie\Translatable`.
