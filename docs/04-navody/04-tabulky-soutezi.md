# Tabulky soutěží (League Standings)

Tato dokumentace popisuje modul pro automatickou synchronizaci a zobrazení tabulek pořadí soutěží, ve kterých týmy klubu hrají.

## Účel
Systém nyní automaticky stahuje kompletní tabulku pořadí ze stránek cz.basketball (např. `/soutez/4999`). Tato data slouží pro:
1.  **Kontrolní součet** - ověření počtu odehraných zápasů a skóre v našem systému oproti oficiální tabulce.
2.  **Informační přehled** - zobrazení tabulky pořadí v administraci i v členské sekci, aby uživatelé měli přehled o svém umístění v soutěži.

## Technický popis

### Datový model
Všechny záznamy z tabulek pořadí jsou uloženy v tabulce `competition_standings`.
-   Každý řádek obsahuje: `rank` (pořadí), `team_name` (název týmu v soutěži), `gp` (odehrané zápasy), `w` (výhry), `l` (prohry), `score` (skóre), `points` (body).
-   Záznamy jsou vázány na `season_id` a `competition_url`.

### Synchronizace
Synchronizace probíhá automaticky v rámci příkazu `php artisan stats:sync-team-season`.
1.  Z URL týmu (např. `/tym/7738`) se automaticky detekuje URL aktuální soutěže.
2.  Z URL soutěže (např. `/soutez/4999`) se stáhne celá tabulka pořadí a uloží do databáze.
3.  Pokud se v tabulce změní data (např. po víkendovém kole), náš systém je při dalším běhu cronu zaktualizuje.

## Zobrazení dat

### V administraci (Filament)
V menu pod skupinou **Statistiky a data** se nachází položka **Tabulky soutěží**.
-   Můžete filtrovat podle **Sezóny**.
-   Pomocí filtru **Náš tým** můžete rychle najít tabulku soutěže, kterou hraje konkrétní tým Sokol Kbely.

### V členské sekci
V levém menu v sekci **Statistiky** přibyla položka **Tabulka soutěže**.
-   Hráči a trenéři zde vidí aktuální tabulku své soutěže.
-   Můžou si přepnout na jinou sezónu nebo na jiný tým klubu (např. se podívat, jak si vedou kolegové z jiných kategorií).
-   Náš tým je v tabulce zvýrazněn (zlaté pozadí a štítek "Náš tým").

## Údržba
Pokud synchronizace hlásí nekonzistenci (např. v našem systému chybí zápasy, které oficiální tabulka již započítala), je to signál pro spuštění plné synchronizace dané sezóny, aby se dohledaly chybějící detaily utkání.
