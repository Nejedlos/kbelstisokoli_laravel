# Robustnost a monitoring externích importů

Tento dokument popisuje mechanismy zajišťující stabilitu, odolnost a sledovatelnost procesu importu sportovních dat z webu `cz.basketball`.

## 1. Fail-safe mechanismy a AI Fallback

Proces synchronizace je navržen tak, aby byl odolný vůči změnám ve struktuře HTML zdrojového webu.

### Strategie extrakce:
1. **DOM Extractor (Primární):** Systém se nejdříve pokusí vyparsovat data pomocí `Symfony DomCrawler` a XPath selektorů. Je to nejrychlejší a nejpřesnější metoda.
2. **AI Normalizer (Fallback):** Pokud DOM extractor selže (např. nenajde očekávanou tabulku nebo dojde k `FatalError`), systém automaticky přepne na AI fallback.
    - HTML fragment je odeslán do LLM (OpenAI GPT-4o).
    - AI se pokusí identifikovat data i v pozměněné struktuře a vrátit je v kanonickém JSON formátu.
    - Běh je označen stavem `partial_failed` s informací, že byl použit AI fallback.
3. **Úplné selhání:** Pokud selže i AI fallback, běh je označen jako `failed`, data se neukládají a administrátor je informován v rozhraní.

## 2. Monitoring a Observability

### Audit běhů (External Import Runs)
Každý pokus o synchronizaci (soupiska, zápasy, detaily) je zaznamenán v tabulce `external_import_runs`.
- **Statusy:** `success`, `skipped` (žádná změna), `partial_failed` (AI fallback), `failed`, `running`.
- **Metadata:** Ukládá se použitý extractor, HTTP status, cesta k HTML snapshotu a počet extrahovaných řádků.
- **Error Summary:** V případě chyby se ukládá zpráva výjimky a stack trace.

### Zdraví synchronizace (Health Check)
V administraci (Filament) je u každé konfigurace týmu a sezóny zobrazen indikátor zdraví:
- **Zelená (OK):** Poslední běhy byly úspěšné.
- **Oranžová (Varování):** 1-2 selhání v řadě.
- **Červená (Kritické):** 3 a více selhání v řadě (indikuje trvalou změnu na webu nebo výpadek zdroje).

## 3. Snapshoty a Debugging

Při každém stažení stránky (fetch) se ukládá raw HTML kód do:
`storage/app/external/czbasketball/{season}/{type}/{id}-{timestamp}.html`

Cesta k tomuto souboru je uložena v metadatech běhu. Administrátor může snapshot využít k analýze chyby parsování.

## 4. Automatizované testování (Regresní testy)

Systém obsahuje sadu testů (`tests/Feature/Stats/Extractors/CzBasketballExtractorTest.php`), které ověřují funkčnost parserů proti uloženým HTML fixturám.
- Testy neběží proti živému webu (nejsou flaky).
- Ověřují, že extraktory nacházejí správné sloupce, ID hráčů a zápasů.

**Spuštění testů:**
```bash
php artisan test --filter=CzBasketballExtractorTest
```

## 5. Údržba a promazávání dat (Retention)

Pro zabránění zaplnění disku snapshoty je implementován příkaz:
`php artisan external-stats:cleanup`

### Pravidla retence:
- **Běhy importů:** Historie se uchovává standardně 3 měsíce.
- **HTML Snapshoty:** Smažou se starší než 30 dní, **s výjimkou** snapshotů z neúspěšných běhů (`failed`, `partial_failed`), které se ponechávají pro pozdější analýzu.

## 6. Inteligentní přeskakování (Performance & Stability)

Pro zamezení zbytečné zátěži systému a zvýšení rychlosti synchronizace (zejména u velkých historických importů) systém využívá inteligentní přeskakování:

### Pravidla přeskakování:
1. **Historické sezóny:** Pokud sezóna není označena jako `is_active` a již má nastaven `last_synced_at`, synchronizace týmu v této sezóně se automaticky přeskakuje.
2. **Historické zápasy:** Pokud je zápas v neaktivní sezóně a již má v metadatech příznak `boxscore_synced_at`, jeho detail (boxscore) se znovu nestahuje.
3. **Historie hráčů:** Při hloubkové synchronizaci historie hráče se neaktivní sezóny přeskakují, pokud pro ně již v databázi existuje alespoň jeden zápas s nastaveným `boxscore_synced_at`. U jednotlivých zápasů se detail stahuje pouze jednou (pokud není vynuceno), přičemž v neaktivních sezónách se absence statistik (např. asistencí) nepovažuje za důvod k opakování pokusu.
4. **Detekce změn (Hash):** U všech synchronizací (soupiska, zápasy, detaily) se porovnává hash staženého HTML fragmentu s posledním úspěšným během. Pokud je hash identický, import se přeskakuje (status `skipped`).

**Vynucení synchronizace:**
Všechna pravidla přeskakování lze obejít použitím příznaku `--force` v CLI příkazech nebo zaškrtnutím volby "Force Sync" v administraci.

### Terminálový výstup a Progress bar (UX):
Všechny synchronizační příkazy (`stats:sync-players`, `stats:sync-team-season`, `stats:sync-match`, `stats:import`) zobrazují v terminálu interaktivní progress bar.
- **Výchozí chování:** Synchronizace probíhá přímo v terminálovém procesu (synchronně), aby uživatel viděl okamžitý postup.
- **Fronta (Queue):** Pro spuštění na pozadí (např. přes cron nebo při velkém objemu dat bez nutnosti sledování) lze použít příznak `--queue`.
- **Hromadný import:** Příkaz `stats:import` slouží pro automatizovanou synchronizaci všech definovaných týmů v aktivní sezóně. I tento příkaz podporuje interaktivní progress bar a bezpečné přerušení.
- **Stavy:** V terminálu se barevně rozlišují výsledky (zelená = úspěch, žlutá = přeskočeno, červená = selhání).

### UI Progress Bar (Basketball Overlay):
Pro sledování synchronizací spuštěných z administrace (nebo i dlouhotrvajících CLI příkazů) je v horní části Filamentu zobrazen interaktivní panel s "glassmorphism" designem a animací basketbalového míče.
- **Sledování všech typů:** Panel automaticky zobrazuje všechny běžící synchronizace (hráči, týmy, zápasy, hromadné importy).
- **Průběžné aktualizace:** Panel se aktualizuje každé 3 sekundy (polling) a zobrazuje aktuálně zpracovávanou položku (např. jméno hráče nebo název týmu a pod-krok jako "Soupiska").
- **Možnost zrušení:** Přímo z panelu lze běžící proces přerušit kliknutím na tlačítko "Zrušit".
- **Detekce zaseknutí:** Pokud se proces nepohne déle než 2 minuty, panel na tuto skutečnost upozorní animovaným nápisem "Možná zaseknuto!".

## 7. Zrušení a detekce zaseknutí (Stuck Detection)

Pro zajištění stability u dlouhotrvajících synchronizací (např. excesivní historie hráčů) je implementován systém kontroly běhu.

### Zrušení synchronizace (Cancel)
Uživatel může běžící synchronizaci zrušit:
1. **Z UI (Filament):** Změnou stavu v `ExternalImportRuns` na `cancelled`. Běžící proces (pokud není zaseknutý) tuto změnu v dalším kroku detekuje a korektně ukončí práci.
2. **V terminálu:** Stisknutím `Ctrl+C`. CLI příkazy reagují na signály `SIGINT/SIGTERM` a před ukončením označí běh v databázi jako `cancelled` s vysvětlující zprávou.

### Detekce zaseknutí (Stuck Detection)
Pokud proces synchronizace přestane odpovídat (např. kvůli neošetřenému síťovému timeoutu nebo zablokování PHP procesu), systém jej dokáže identifikovat:
- **Pravidlo:** Pokud se stav `updated_at` u běžící synchronizace (`status = running`) nepohne déle než 30 minut, je považována za zaseknutou.
- **Automatické čištění:** Příkaz `php artisan stats:check-stuck-syncs` prochází všechny běžící importy a ty zaseknuté označí stavem `stuck`. Tento příkaz je doporučeno spouštět periodicky přes Scheduler.

## 8. Co dělat, když se změní HTML?

Pokud indikátor zdraví v administraci zčervená:
1. Zkontrolujte `error_summary` u posledních běhů v `ExternalImportRuns`.
2. Otevřete HTML snapshot a porovnejte jej s očekávanou strukturou.
3. Spusťte regresní testy lokálně.
4. Aktualizujte XPath selektory v příslušném extraktoru (`app/Services/Stats/Extractors/CzBasketball/`).
5. Po opravě a nasazení spusťte synchronizaci ručně pomocí tlačítka "Sync" ve Filamentu.
