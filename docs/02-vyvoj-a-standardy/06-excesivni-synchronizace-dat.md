# Excesivní synchronizace dat (cz.basketball)

Tento dokument popisuje strategii a strukturu "excesivní" extrakce a uložení dat z externího zdroje `cz.basketball` pro hráče a zápasy.

## 1. Cíl synchronizace
Cílem je získat a uložit maximum dostupných informací o každém spárovaném hráči, včetně jeho kompletní historie na portálu `cz.basketball`. To zahrnuje nejen sumární statistiky sezón, ale i detailní statistiky každého jednotlivého zápasu v jeho kariéře (asistence, doskoky, bloky atd.).

## 2. Architektura extrakce

### 2.1 Hráč (PlayerSyncService)
Synchronizace hráče probíhá v několika vlnách:
1. **Základní profil:** Výška, pozice, ročník narození, aktuální klub a fotografie.
2. **Kariéra (Sumáře):** Extrakce všech sezón z tabulky "Kariéra" do `external_player_stats`.
3. **Rekordy:** Osobní rekordy (nejvíce bodů, doskoků v zápase) se ukládají do `metadata` v `PlayerProfile`.
4. **Historie zápasů (Excesivní):**
    - Služba identifikuje všechny dostupné sezóny v profilu hráče.
    - Pro každou sezónu se stáhne seznam zápasů (`?tab=matches&season=...`).
    - Každý nalezený zápas je uložen do `external_player_matches`.
    - Pro každý zápas je následně stažen jeho detail (boxscore).

### 2.2 Tým a Zápas (ExternalStatsSyncService)
Synchronizace týmu/sezóny může probíhat ve dvou režimech:
1. **Standardní:** Synchronizuje soupisku a seznam zápasů. Detaily zápasů stahuje prioritně jen pro ty, které proběhly nedávno (výchozí 3 dny) nebo u kterých v DB chybí skóre. Má nastaven limit (výchozí 15) na počet detailních synchronizací v jednom běhu.
2. **Excesivní (`--excesive`):**
    - Ignoruje veškeré časové a množstevní limity.
    - Prochází **všechny** zápasy v sezóně, které mají externí ID.
    - Vynucuje stažení a aktualizaci kompletního boxscoru pro každý zápas.
    - Stahuje a ukládá kompletní metadata z hlavičky zápasu (rozhodčí, hala, diváci).
    - Provádí hloubkovou extrakci všech hráčů v zápase a jejich statistik.

### 2.3 Detaily zápasu (MatchDetailBoxscoreExtractor)
Detailní synchronizace zápasu (`syncMatchDetail`) získává maximum dostupných dat:
- **Boxscore:** Kompletní tabulka statistik pro všechny hráče obou týmů.
- **Srovnání týmů:** Týmové statistiky (střelba, doskoky, ztráty).
- **Nejlepší hráči:** Identifikace leaderů zápasu.
- **Historie vzájemných zápasů:** Seznam předchozích střetnutí.

### 2.3 Propojení (Deep Sync)
Při zpracování boxscoru libovolného zápasu služba `StatisticSyncService` automaticky identifikuje naše hráče (podle `ExternalEntityMapping`) a aktualizuje jejich záznamy v `external_player_matches` o detailní statistiky, které v seznamu zápasů na profilu hráče chybí (např. asistence, zisky, plus_minus).

## 3. Databázová struktura

### 3.1 `external_player_stats`
Slouží pro sumární statistiky za celé sezóny nebo soutěže. Obsahuje průměry (points_avg, assists_avg atd.).

### 3.2 `external_player_matches` (Excesivní)
Detailní tabulka pro historii zápasů konkrétního hráče.
**Rozšířená pole:**
- `number` (číslo dresu), `is_starter`, `is_captain`
- `points`, `two_points_made/attempts`, `three_points_made/attempts`, `free_throws_made/attempts/pct`
- `rebounds_offensive/defensive/total`
- `assists`, `steals`, `turnovers`, `blocks`, `fouls`, `fouls_drawn`
- `minutes`, `valuation` (užitečnost), `plus_minus`
- `scheduled_at` (přesné datum a čas), `venue` (místo konání), `metadata` (další data z hlavičky zápasu)
- `basketball_match_id` (cizí klíč do hlavní tabulky `matches`)

## 4. Budoucí zápasy a predikce
Synchronizace nyní podporuje i zápasy, které se teprve odehrají.
- **Detekce času:** Pokud boxscore v hlavičce (v poli pro skóre) obsahuje čas (např. "19:15"), je tento čas automaticky spárován s datem zápasu a uložen do `scheduled_at`.
- **Metadata:** U budoucích zápasů se stahují dostupná metadata (místo konání, rozhodčí), která slouží pro predikce a plánování.

## 5. Automatické zakládání zápasů (BasketballMatch)
Služba `PlayerSyncService` při synchronizaci detailu zápasu hráče automaticky provádí pokus o založení nebo aktualizaci záznamu v hlavní tabulce `matches`:
1. **Identifikace týmu:** Pokud název týmu v hlavičce (Home/Away) odpovídá některému z našich interních týmů (v tabulce `teams`), je zápas označen jako "náš".
2. **Oponent:** Pokud oponent v naší DB neexistuje, je automaticky vytvořen v tabulce `opponents`.
3. **Sezóna:** Zápas je automaticky zařazen do správné sezóny (např. 2025/2026).
4. **Stav:** Budoucí zápasy jsou založeny se statusem `scheduled`, odehrané se statusem `completed` a uloženým skóre.
5. **Haly (Venues):** Pokud se v hlavičce zápasu nachází informace o místě konání (hala), systém automaticky vytvoří záznam v tabulce `venues` a propojí jej se zápasem. Pokud se jedná o domácí zápas (Home), je tato hala nastavena jako primární pro náš tým (pokud ještě žádnou nemá). Obdobně se systém "učí" primární haly i pro oponenty.
6. **Propojení:** Vzniká vazba mezi individuálním výkonem hráče (`external_player_matches`) a týmovým zápasem (`matches`).

## 6. Správa hal (Venues)
Systém implementuje automatickou správu hal na základě dat ze zápasů:
- **Unifikace:** Haly jsou identifikovány primárně podle názvu. V `metadata` jsou uloženy původní tvary názvů pro budoucí párování.
- **Vazba na týmy:** Každý `Team` a `Opponent` může mít přiřazenou `primary_venue_id`. Tato vazba se automaticky doplňuje při synchronizaci prvního domácího zápasu daného týmu.
- **Geolokace:** Struktura tabulky `venues` je připravena pro uložení adresy a GPS souřadnic, které mohou být doplněny manuálně nebo externí službou.

## 7. Algoritmus extrakce podstránek
Služba implementuje mechanismus "sledování odkazů":
1. Profil hráče -> Seznam sezón.
2. Seznam sezón -> Odkazy na seznamy zápasů.
3. Seznamy zápasů -> Odkazy na detaily zápasů (boxscore).
4. Boxscore -> Odkazy na další hráče v týmu.

## 5. Konfigurace a kredity
Vzhledem k požadavku na maximální detailnost je synchronizace navržena tak, aby prováděla více HTTP požadavků. 
- Pro optimální výkon se doporučuje spouštět hloubkovou synchronizaci v pozadí (Queue).
- Extrakce využívá robustní DOM extraktory, které jsou odolnější než čisté AI parsování u vysoce strukturovaných tabulek.

8. Sledování průběhu (Progress Tracking)
Vzhledem k časové náročnosti "excesivní" synchronizace (velké množství HTTP požadavků na historii hráče) systém implementuje globální sledování průběhu:
- **Záznamy o běhu:** Každá synchronizace (dávková i excesivní) vytváří záznam v `external_import_runs`.
- **Progres v reálném čase:** Služby průběžně aktualizují `progress_percent` a `current_item_label` (např. "Hráč: Ondřej Bartoška", "Sezóna: 2023/24").
- **UI Komponenta:** Volt komponenta `SyncStatusBar` automaticky detekuje běžící importy a zobrazuje fixní stavovou lištu v horní části obrazovky.
- **Viditelnost a zabezpečení:** 
    - Stavová lišta je viditelná **pouze pokud existuje aktivní běh** (stav `running`).
    - Zobrazení je omezeno **pouze na přihlášené uživatele s oprávněním `manage_stats`**. Ostatní uživatelé (včetně běžných návštěvníků webu) stavovou lištu nevidí, aby nebyli rušeni technickými procesy na pozadí.
    - Lišta je integrována do všech částí systému (veřejný web, členská sekce, administrace).

## 9. Systémová konzole (System Console)
Pro manuální spouštění a testování synchronizace slouží **System Console** v administraci.

### 9.1 Dostupné příkazy
- **Statistiky: Hráči (Sync) (`stats:sync-players`):**
    - Umožňuje synchronizaci konkrétního hráče zadáním jeho **interního ID uživatele**.
    - Podporuje přepínač `--excesive` pro stažení kompletní historie a boxscorů.
    - Podporuje `--force` pro vynucení stažení dat bez ohledu na cache.
- **Statistiky: Tým (Sync) (`stats:sync-team-season`):**
    - Vyžaduje zadání **slugu týmu** (např. `sokol-kbely-c`).
    - Podporuje `--excesive` pro hloubkovou synchronizaci všech zápasů v aktuální sezóně.
    - Podporuje `--sync` pro automatické založení zápasů v interním kalendáři.
- **Statistiky: Hromadný Import (`stats:import`):**
    - Slouží pro automatizovanou pipeline (všechny týmy najednou).
    - Podporuje `--recent` pro rychlou aktualizaci pouze posledních zápasů.

### 9.2 Rozhraní konzole
V konzoli jsou k dispozici textová pole pro zadání parametrů (`ID Uživatele`, `Slug Týmu`) a checkboxy pro zapnutí excesivního režimu. Příkazy lze spouštět buď standardně přes shell, nebo jako **Internal Execution** (přímo v PHP procesu), což je užitečné při problémech s CLI binárkou na hostingu.

## 10. Stabilita a ochrana proti zablokování (Throttling)

Vzhledem k "excesivnímu" charakteru synchronizace, která může v jednom běhu vygenerovat stovky HTTP požadavků na externí server `cz.basketball`, systém implementuje několik mechanismů pro zajištění stability a ohleduplnosti:

1.  **Mikropauzy (usleep):**
    - Mezi požadavky na jednotlivé boxscory zápasů je v excesivním režimu vložena pauza **0.8s**.
    - Mezi požadavky na seznamy sezón hráče je pauza **0.5s**.
    - Mezi zpracováním jednotlivých hráčů v hromadné synchronizaci je pauza **0.3s**.
    - Mezi týmy/sezónami v hromadném importu je pauza **0.5s až 1.5s** (podle velikosti dávky).
2.  **Inteligentní Skip:** Systém stahuje detail zápasu (boxscore) pouze v případě, že zápas již proběhl (je v minulosti nebo má skóre) a zároveň v naší databázi chybí detailní statistiky (např. asistence). Tím se drasticky snižuje počet opakovaných požadavků při pravidelném běhu.
3.  **Podpora pro ukončení:** Všechny synchronizační cykly kontrolují stav `ConsoleService::isStopped()`, což umožňuje bezpečné a okamžité zastavení hromadných operací z administrace nebo signálem systému bez rizika poškození dat.
4.  **Dávkování ve frontě:** Při asynchronním zpracování (Queue) jsou úlohy pro detaily zápasů do fronty vkládány s postupným zpožděním (`delay`), aby se nespustily všechny naráz.

---
*Poslední aktualizace: 14. 3. 2026*
