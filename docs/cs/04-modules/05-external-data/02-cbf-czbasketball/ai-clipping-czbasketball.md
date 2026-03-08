# AI Clipping & Normalize Pipeline (CZ.BASKETBALL)

Tento modul zajišťuje robustní vytěžování dat z webu cz.basketball pomocí kombinace cíleného vyřezávání HTML fragmentů (clipping) a následné AI normalizace do strukturovaného JSONu.

## Principy fungování

1.  **Clipper (Vyřezávač):** Než jsou data odeslána AI, projde celá HTML stránka "clipperem". Ten identifikuje relevantní tabulky a sekce (soupiska, seznam zápasů, boxscore) a vyřízne pouze jejich bezprostřední okolí.
    *   **Heuristika:** Clipper nepoužívá pevné selektory, které se často mění, ale heuristiku založenou na obsahu (např. "tabulka, kde je alespoň 3 řádky s odkazem na hráče").
    *   **Sanitizace:** Vyříznutý fragment je agresivně sanitizován (odstranění stylů, skriptů, SVG a nepotřebných atributů), čímž se zmenší velikost dat pro AI o 80-90%.

2.  **AI Normalizer:** Sanitizovaný fragment je poslán do OpenAI (model `gpt-4o-mini`) spolu se **striktním JSON schématem**.
    *   AI vrací data v unifikovaném formátu bez ohledu na to, jak vypadá zdrojová tabulka.
    *   Součástí promptu jsou i canonical keys pro správné mapování statistik.

## Typy klipů a schémata

### 1. `team_header`
*   **Zdroj:** Hlavní nadpis a info blok na stránce týmu.
*   **Vytěžuje:** Název týmu, soutěž, sezónu, halu, trenéra.

### 2. `roster_table`
*   **Zdroj:** Tabulka se soupiskou.
*   **Vytěžuje:** `player_external_id` (z odkazu `/hrac/{id}`), jméno, ročník, pozici, číslo dresu.

### 3. `matches_list`
*   **Zdroj:** Seznam zápasů (na stránce týmu nebo v přehledu `/zapasy`).
*   **Vytěžuje:** `match_external_id` (z odkazu `/zapas/{id}`), datum, týmy, skóre, kolo.
*   **Chunking:** Pokud je seznam moc dlouhý, clipper jej automaticky rozdělí na menší části po 40 řádcích.

### 4. `boxscore_home` / `boxscore_away`
*   **Zdroj:** Detail zápasu `/zapas/{id}`.
*   **Vytěžuje:** Kompletní statistiky hráčů (body, minuty, fauly, střelba atd.).

## Konfigurace a režimy

*   **`CZBASKETBALL_AI_ONLY=true`**: V tomto režimu se zcela přeskakují klasické DOM extraktory a všechna data se těží přes AI (clipping stále probíhá pro úsporu tokenů).
*   **AI Fallback**: Pokud je `AI_ONLY` vypnuto, AI se použije automaticky v případě, že DOM extraktor selže nebo vrátí nekompletní data (např. chybějící skóre u odehraného zápasu).

## Debugování a Observability

V administraci v sekci **Historie importů** (`ExternalImportRuns`) lze u každého běhu vidět:
*   `clips_found`: Seznam ID fragmentů, které clipper našel.
*   `debug_html_file`: Odkaz ke stažení sanitizovaného HTML, které viděla AI.
*   `error_summary`: Detailní popis chyby včetně délky promptu a použitého timeoutu.

## Související soubory
*   `App\Services\Stats\Clippers\CzBasketball\*` (Logika vyřezávání)
*   `App\Services\Stats\Normalizers\OpenAiNormalizer` (Komunikace s AI)
*   `App\Services\Stats\Sync\ExternalStatsSyncService` (Orchestrace pipeline)
