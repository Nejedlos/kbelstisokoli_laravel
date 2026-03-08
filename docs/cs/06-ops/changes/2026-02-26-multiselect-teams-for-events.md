# Změna: Multiselect týmů pro tréninky a klubové akce

**Datum:** 2026-02-26
**Autor:** Junie
**Status:** Implementováno

## Účel
Tato změna umožňuje přiřadit trénink nebo klubovou akci k více týmům najednou. To řeší situaci, kdy probíhá společný trénink pro dva týmy (např. Muži C a Muži E) nebo klubová akce určená pouze pro vybranou skupinu týmů.

## Provedené změny

### 1. Databáze (Migrace)
- Vytvořeny pivot tabulky `team_training` a `club_event_team` pro vazbu M:N.
- Stávající data byla převedena z původních sloupců `team_id` (tabulky `trainings` a `club_events`) do nových pivot tabulek.
- Původní sloupce `team_id` byly odstraněny z tabulek `trainings` a `club_events`.

### 2. Modely (`app/Models/`)
- **Training:** Změna relace `team()` (belongsTo) na `teams()` (belongsToMany).
- **ClubEvent:** Změna relace `team()` (belongsTo) na `teams()` (belongsToMany).
- **Team:** Přidány/upraveny relace `trainings()` a `clubEvents()` jako `belongsToMany`.

### 3. Administrace (Filament)
- **Formuláře:** `TrainingForm` a `ClubEventForm` nyní používají `Select` s příznakem `multiple()` pro výběr týmů.
- **Tabulky:** V přehledu tréninků a akcí se nyní týmy zobrazují jako seznam štítků (badges).
- **Filtry:** Filtrování v tabulce klubových akcí bylo aktualizováno, aby fungovalo přes relaci `teams`.

### 4. Členská sekce (Member Section)
- **TeamController:** Upraveno filtrování nadcházejících tréninků. Nyní se používá `whereHas('teams', ...)` pro zobrazení tréninků, které patří danému týmu.
- **AttendanceController:** Aktualizovány relace a opravy chyb v ukládání docházky (změna sloupce `status` na `planned_status` podle schématu tabulky `attendances`).

## Technické detaily
Pro kontrolu, zda má uživatel vidět událost, se nyní používá standardní Laravel Eloquent dotaz:
```php
$upcomingTrainings = Training::whereHas('teams', fn($q) => $q->where('teams.id', $teamId))
    ->where('starts_at', '>=', now())
    ->get();
```

## Docházka
Pokud je akce pro více týmů, záznamy docházky v `AttendancesRelationManager` se stále vážou na konkrétního uživatele. Při výpisu docházky v členské sekci systém správně spáruje uživatele s událostí na základě jeho příslušnosti k jednomu z vybraných týmů.
