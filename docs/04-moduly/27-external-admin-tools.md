# Administrace externích statistik a synchronizace

Tento modul poskytuje administrativní rozhraní pro správu mapování týmů, konfiguraci sezón, auditování importů a manuální párování hráčů. Všechny nástroje jsou dostupné v sekci **Externí statistiky**.

## 1. Externí zdroje statistik (External Stat Sources)
Centrální správa zdrojů dat (např. `cz.basketball`).
- **Přiřazené týmy:** Zobrazuje seznam všech týmů, které jsou k tomuto zdroji namapovány, s rychlým odkazem na editaci týmu.
- **Správa mapování:** V detailu zdroje lze přímo přidávat nebo odebírat přiřazené týmy (Relation Manager).
- **Akce Synchronizovat vše:** Spustí hromadnou synchronizaci všech připojených týmů.

## 2. Mapování týmů (External Team Mappings)
Slouží k definici základní identity týmu napříč sezónami.
- **V detailu týmu:** Mapování lze spravovat přímo v administraci týmu (sekce *Externí synchronizace*).
- **Interní tým:** Výběr týmu z našeho systému.
- **Zdroj:** Výběr z dostupných externích zdrojů (např. `cz.basketball`).
- **Externí ID:** Unikátní identifikátor týmu na webu (např. `7738`).
- **Základní URL:** Odkaz na domovskou stránku týmu.

## 3. Konfigurace sezón (External Team Season Configs)
Sezónně specifické nastavení pro synchronizaci.
- **Externí rok sezóny:** Parametr `y`, který určuje sezónu (např. `2025` pro 2025/26).
- **URL soupisky a zápasů:** Konkrétní adresy, ze kterých se čerpají data.
- **Akce:**
    - **Sync:** Spustí okamžitou synchronizaci (soupiska + seznam zápasů + naplánování detailů).
    - **Dry-run:** Stáhne data a zobrazí náhled (roster, zápasy) v modálním okně bez uložení do databáze.
    - **Force Sync:** Ignoruje hashy a vynutí přepracování všech dat.

## 3. Párování hráčů (Player Mappings)
Kritický nástroj pro propojení externích statistik s interními uživateli.
- **Nespárovaní hráči:** Seznam hráčů nalezených na soupiskách nebo v zápasech, kteří nemají přiřazeného uživatele.
- **Akce Spárovat:** Umožňuje vybrat interního uživatele. Po uložení systém automaticky:
    1. Propojí externí identitu s uživatelem.
    2. Aktualizuje `player_id` u všech historických i budoucích statistických řádků daného hráče.
    3. Spustí přepočet sezónních souhrnů (body, průměry atd.).
- **Akce Přepočítat:** Ruční vyvolání přepočtu statistik pro již spárovaného hráče.

## 4. Historie importů (Import Runs)
Detailní audit všech proběhlých pokusů o import.
- **Statusy:** `success`, `skipped` (nic se nezměnilo), `failed`, `partial_failed`.
- **Indikátor AI:** Ikona jiskry značí, že byl při importu využit AI fallback (LLM normalizace) místo standardního DOM parsování.
- **Metadata:** Obsahují detailní počty extrahovaných řádků, chyby a technické parametry.

## 5. Provozní pokyny
1. **Nový tým/sezóna:** Nejdříve vytvořte Mapování týmu, poté Konfiguraci sezóny.
2. **Pravidelná kontrola:** Jednou týdně zkontrolujte sekci *Párování hráčů* a propojte nově objevené hráče ("Ghost uživatele").
3. **Ladění:** Pokud synchronizace hlásí chybu, podívejte se do *Historie importů* na konkrétní `error_summary`.
