# Admin: Přestavba stránky Tabulky soutěží (Competition Standings)

Datum: 2026-04-09

## Cíl
Zlepšit použitelnost stránky `Admin → Competition Standings`:
- Rozdělení zobrazení podle našich týmů (Tabs).
- Jednotná volba sezóny jako filtr tabulky.
- Odstranění řádkových akcí (Edit, Delete, Sync) a tlačítka Create.
- Zachování akce „Synchronizovat“ v hlavičce s respektem k aktivnímu týmu (tab) a zvolené sezóně.

## Změny v kódu
- `app/Filament/Resources/CompetitionStandings/Tables/CompetitionStandingsTable.php`
  - Odebrán filtr `team_id`; ponechán pouze `season_id`.
  - Odstraněny všechny řádkové akce a bulk akce.
- `app/Filament/Resources/CompetitionStandings/Pages/ListCompetitionStandings.php`
  - Přidána metoda `getTabs()` s Tab pro „Všechny týmy“ a pro každý tým (`Team`).
  - Úprava akce „Synchronizovat“ v hlavičce: synchronizuje buď všechny soutěže zvolené sezóny, nebo pouze soutěže aktivního týmu (dle aktivního tabu).
  - Odstraněn `CreateAction` z hlavičky.

## Poznámky k implementaci
- Tabs filtrují dotaz podle `competition_url` z tabulky `external_team_season_configs` (stav `is_enabled = true`), s ohledem na zvolenou sezónu (filtr tabulky `season_id`).
- Akce „Synchronizovat“ používá `CompetitionSyncService`:
  - Pro konkrétní tým volá opakovaně `syncStandingsOnly($url, $season)` nad URL soutěží daného týmu v sezóně.
  - Pro všechny týmy volá `syncAllStandings($season)`.

## Generátory (Artisan/Filament)
- V tomto úkolu nebyly použity žádné generátory. Neproběhly žádné interaktivní příkazy.

## UX dopad
- Uživateli zůstává centrální filtr „Sezóna“ a přehledné přepínání týmů přes Tabs.
- Řádkové akce byly odstraněny podle standardů projektu; synchronizace je dostupná jediným tlačítkem v hlavičce.
