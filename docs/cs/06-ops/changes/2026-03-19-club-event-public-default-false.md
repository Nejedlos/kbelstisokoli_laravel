# Změna: Výchozí viditelnost klubových akcí (is_public)

## Popis změny
Na základě požadavku uživatele byla změněna výchozí hodnota pole **Veřejná akce?** (`is_public`) při vytváření nové klubové akce.

Původně byla každá nová akce nastavena jako veřejná (`true`). Nyní je výchozí stav **neveřejná** (`false`).

## Technické detaily
- **Filament Schéma:** V souboru `app/Filament/Resources/ClubEvents/Schemas/ClubEventForm.php` byla změněna metoda `default(true)` na `default(false)` u komponenty `Toggle::make('is_public')`.
- **Databáze:** Byla vytvořena migrace `2026_03_19_123121_change_default_is_public_in_club_events_table.php`, která mění výchozí hodnotu sloupce `is_public` v tabulce `club_events` na `false`.
- **Dokumentace/Help:** Byly aktualizovány help soubory `database/seeders/Help/content/cs/sport/klubove-akce.md` (a anglická verze), aby reflektovaly tuto změnu.

## Dopad
Při zadávání nové akce v administraci nebude akce automaticky viditelná na webu, dokud ji admin ručně nepřepne na "Veřejná akce". To zabrání nechtěnému zveřejnění rozpracovaných nebo interních akcí.
