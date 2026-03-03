# Audit importů a Idempotence

Tento dokument popisuje mechanismus auditování běhů externích importů a zajištění idempotence pomocí hashování HTML fragmentů.

## 1. Audit běhů (`ExternalImportRun`)

Každý pokus o import (z team page, seznamu zápasů, detailu zápasu nebo hráče) je zaznamenán v tabulce `external_import_runs`. To umožňuje:
- **Sledování historie:** Kdy a s jakým výsledkem import proběhl.
- **Ladění (Debugging):** Ukládání chybových hlášení (`error_summary`) a metadat o průběhu.
- **Statistiky:** Počty extrahovaných, importovaných a přeskočených záznamů.

### Stavy importu (`status`):
- `queued`: Import je naplánován.
- `running`: Import právě probíhá.
- `success`: Import proběhl v pořádku a data byla změněna.
- `partial_failed`: Část dat byla importována, ale došlo k chybám.
- `failed`: Import selhal jako celek.
- `skipped`: Import byl přeskočen, protože se zdrojová data nezměnila (shodný hash).

## 2. Idempotence a Fragment Hashing

Abychom předešli zbytečnému přepisování dat v databázi a šetřili prostředky (zejména při použití AI normalizace), používáme mechanismus hashování obsahu.

### Jak to funguje:
1. **Extrakce fragmentu:** `StatExtractor` nevyseparuje pouze data (pole), ale vrátí i surový HTML fragment (`fragment_html`), který tato data reprezentuje (např. konkrétní `<table>` nebo `<div>`).
2. **Výpočet hashe:** Ze staženého fragmentu se vypočítá SHA256 hash (`content_hash`).
3. **Kontrola změny:** Před spuštěním samotného procesu importu/normalizace se systém podívá na poslední **úspěšný** (`success`) nebo **přeskočený** (`skipped`) záznam v `external_import_runs` pro:
    - stejný `source_key` (např. "czbasketball")
    - stejný `run_type` (např. "match_detail")
    - stejný `target_external_id` (např. ID zápasu "519196")
4. **Rozhodnutí:**
    - Pokud se nový hash shoduje s posledním uloženým hashem, import se označí jako `skipped` a dále nepokračuje.
    - Pokud se hash liší (nebo neexistuje předchozí záznam), spustí se plný import a po úspěšném dokončení se uloží nový hash.

## 3. Debugování a Metadata

V poli `metadata` modelu `ExternalImportRun` se ukládají doplňkové informace, které pomáhají při analýze problémů:
- Použité CSS selektory.
- URL, ze které se stahovalo.
- Varování, která nezpůsobila selhání, ale jsou relevantní.
- Informace o verzi parseru.

Při selhání (`failed`) se do `error_summary` ukládá text výjimky nebo popis chyby.
