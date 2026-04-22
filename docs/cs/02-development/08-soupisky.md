# Soupisky týmů

Tento modul zajišťuje zobrazení aktuálních soupisek pro týmy, které mají v databázi přiřazené hráče na soupisce pro aktuální sezónu.

## Technické řešení

### Model a data
- Hráči jsou na soupisku týmu přiřazeni skrze vztah `rosterPlayers` v modelu `Team`.
- Vztah využívá pivot tabulku `player_profile_team` s příznakem `is_on_roster = 1`.
- Data hráčů jsou čerpána z modelu `PlayerProfile`, který je propojen s modelem `User`.

### Routing a Controller
- Routa: `/tymy/soupisky` (název `public.teams.roster`).
- Controller: `App\Http\Controllers\Public\TeamController@roster`.
- Logika: Načte všechny týmy, které mají alespoň jednoho hráče na soupisce. Hráči jsou v rámci týmu seřazeni podle příjmení (`User->last_name`).

### Frontend
- View: `resources/views/public/teams/roster.blade.php`.
- Design: Karty hráčů s fotkou, číslem dresu a pozicí. Pokud hráč nemá fotku, zobrazí se placeholder.
- SEO: Stránka využívá globální SEO metadata pro "týmy" jako základ.

### Lokalizace
- Navigace: `nav.roster` v `lang/cs.json` a `lang/en.json`.
- Texty: `teams.roster_title` atd. v `lang/cs/teams.php` a `lang/en/teams.php`.
- Jména týmů jsou překládána automaticky skrze `spatie/laravel-translatable`.

## Použití
Soupisky se v menu zobrazují automaticky pod odkazem "Soupisky". Stránka dynamicky reaguje na změny v administraci (přidání/odebrání hráče ze soupisky).
