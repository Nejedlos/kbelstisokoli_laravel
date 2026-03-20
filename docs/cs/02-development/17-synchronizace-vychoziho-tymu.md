# Synchronizace výchozího týmu člena

Tento modul zajišťuje, aby každý aktivní člen (hráč nebo trenér) měl v uživatelském profilu správně nastavený výchozí tým pro zobrazení v členské sekci.

## Účel
Aby uživatel po přihlášení do členské sekce viděl data relevantní pro jeho tým (např. soupisku, docházku), je nutné mít v DB nastaveno `member_default_team_id`. Protože se soupisky mohou v průběhu sezóny měnit, probíhá tato synchronizace automaticky.

## Technické řešení
Synchronizaci zajišťuje Artisan příkaz:
```bash
php artisan app:sync-member-default-teams
```

### Logika určování týmu
Příkaz prochází všechny aktivní uživatele a zjišťuje jejich příslušnost k týmům ve dvou rolích:
1. **Hráč na soupisce:** Uživatel má aktivní hráčský profil, který je v tabulce `player_profile_team` označen příznakem `is_on_roster = true`.
2. **Trenér:** Uživatel je přiřazen k týmu v tabulce `coach_team`.

Na základě počtu nalezených unikátních týmů se nastavují pole v tabulce `users`:
- **Právě 1 tým:**
    - `member_default_team_id` = ID daného týmu.
    - `member_view_all_by_default` = `false`.
- **Více než 1 tým:**
    - `member_default_team_id` = `null`.
    - `member_view_all_by_default` = `true` (uživateli se v členské sekci defaultně zobrazí přehled všech jeho týmů).
- **Žádný tým:**
    - `member_default_team_id` = `null`.
    - `member_view_all_by_default` = `false`.

## Plánování (Schedule)
Příkaz je zaregistrován v `routes/console.php` a spouští se **každou hodinu**:
```php
Schedule::command('app:sync-member-default-teams')->hourly();
```

## Ruční spuštění
V případě potřeby (např. po velkém importu dat nebo ruční změně soupisek) lze synchronizaci vynutit na produkci příkazem:
```bash
php8.4 artisan app:sync-member-default-teams
```
