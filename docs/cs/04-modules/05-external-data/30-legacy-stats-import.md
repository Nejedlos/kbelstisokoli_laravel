# Modul: Legacy Stats Import (Import historických statistik)

Tento modul umožňuje administrátorům hromadně nahrávat a zpracovávat historické statistiky z HTML souborů (staré weby, archivy apod.).

## 1. Základní workflow

1.  **Vytvoření dávky (Batch):** Admin vytvoří novou dávku, pojmenuje ji a do dropzony nahraje jeden nebo více HTML souborů.
2.  **Klasifikace (Auto-detect):** Po uložení systém automaticky zanalyzuje každý soubor (název a obsah) a pokusí se zjistit:
    -   **Sezónu:** (např. "2015/2016" z regexu 2015-16).
    -   **Tým:** (detekce "Kbely C", "Kbely E" z textu).
    -   **Typ souboru:** (`players_stats`, `team_stats`, `league_table`).
3.  **Náhled (Preview):** V detailu dávky může admin u každého souboru kliknout na ikonu oka (Náhled) a uvidí tabulku tak, jak ji systém vyparsoval (prvních 15 řádků).
4.  **Spuštění importu:** Tlačítkem "Spustit import" se soubory zařadí do fronty (`ProcessLegacyImportBatchJob`).
5.  **Zpracování:** Každý soubor je zpracován samostatným jobem, který:
    -   Zkontroluje **content_hash** pro zabránění duplicitám (idempotence).
    -   Zajistí existenci sezóny v tabulce `seasons`.
    -   Vytvoří/najde `StatisticSet` pro daný typ a sezónu (např. `legacy-players-stats-2015-2016`).
    -   Uloží data do `statistic_rows`.

## 2. Technické detaily

### Modely
- `LegacyImportBatch`: Zastřešuje celou operaci, drží celkový status a počty.
- `LegacyImportFile`: Reprezentuje jeden HTML soubor, jeho cestu, hash a výsledek parsování.

### Extrakce dat (LegacyStatExtractor)
Systém používá `Symfony DomCrawler` k vyhledání první tabulky v HTML souboru. Hlavičky jsou automaticky mapovány na **canonical keys**:
-   `hráč`, `jméno` -> `player_name`
-   `body`, `b` -> `pts`
-   `zápasy`, `z` -> `gp`
-   `2b`, `3b`, `th` -> `fg2_made`, `fg3_made`, `ft_made`
-   ...atd.

### Idempotence
Při každém importu souboru se počítá `sha256` hash jeho obsahu. Pokud systém narazí na soubor se stejným hashem, který už byl úspěšně importován (i v jiné dávce), označí jej jako `skipped` a data znovu nevkládá.

### Párování hráčů
U historických dat často chybí unikátní ID (licenční číslo). Data jsou proto vkládána s `player_id = null` a jméno je uloženo v `row_label`. Administrátor může později využít nástroje pro párování (viz modul Externí statistiky).

## 3. Administrace (Filament)

Modul naleznete v sekci **Externí statistiky > Import historických dat**.
-   **Seznam dávek:** Přehled všech importů a jejich stavu.
-   **Detail dávky:** Progress bar, počty úspěchů/chyb a tabulka souborů s logem chyb.
-   **Náhled:** Umožňuje ověřit správnost parsování před samotným spuštěním importu do DB.

## 4. Debugging a chyby

-   Pokud import selže, status souboru se změní na `failed` a v detailu je zobrazen `error_summary`.
-   Každý běh importu vytváří záznam v `ExternalImportRun` pro auditní účely.
-   HTML snapshoty jsou uloženy v `storage/app/public/legacy_import/{batch_id}/`.
