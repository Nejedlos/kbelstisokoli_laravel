# DOM/XPath Extraktory pro cz.basketball

Tento dokument popisuje technickou implementaci extraktorů (`StatExtractorInterface`) pro získávání sportovních dat z webu `cz.basketball`.

## 1. Architektura
Všechny extraktory jsou postaveny na komponentě `Symfony DomCrawler`. Primární strategií je nalezení relevantní HTML tabulky a její transformace do `NormalizedTableDTO`. Pokud extrakce selže, systém v budoucnu využije AI normalizaci jako fallback.

Každý extraktor vrací:
- `data`: Objekt `NormalizedTableDTO` se strukturovanými daty.
- `fragment_html`: Surový HTML fragment (např. `<table>`), který se používá pro generování hashu a detekci změn (idempotence).

---

## 2. Implementované extraktory

### 2.1 `TeamRosterExtractor`
*Účel: Získání soupisky hráčů ze stránky týmu.*
- **URL:** `/tym/{teamId}?y={year}`
- **Selektor tabulky:** `table.js-table-fixed-order` (fallback na první tabulku s odkazy na `/hrac/`).
- **Extrahovaná pole:**
    - `player_name`: Jméno hráče z odkazu nebo textu buňky.
    - `birth_year`: Rok narození (vyhledání 4-místného čísla 19xx/20xx).
    - `external_player_id`: ID z URL `/hrac/{id}`.

### 2.2 `MatchesListExtractor`
*Účel: Získání seznamu zápasů a jejich výsledků.*
- **URL:** `/zapasy?c={teamId}&y={year}` (obvykle na subdoméně `smo.cz.basketball`).
- **Selektor tabulky:** `table.table-striped` (fallback na první tabulku s odkazy na `/zapas/`).
- **Extrahovaná pole:**
    - `scheduled_at`: Datum a čas začátku (parsování formátu `j. n. Y H:i`).
    - `home_team`, `away_team`: Názvy týmů z buňky (vyhledání přes `.text-nowrap`).
    - `score`: Výsledek zápasu.
    - `status`: `completed` (pokud je přítomno skóre) nebo `planned`.
    - `external_match_id`: ID z URL `/zapas/{id}`.

### 2.3 `MatchDetailBoxscoreExtractor`
*Účel: Získání detailních statistik (boxscore) z konkrétního zápasu.*
- **URL:** `/zapas/{id}`
- **Selektor tabulky:** `table.table-condensed.table-bordered` (ignoruje tabulky s méně než 5 sloupci).
- **Hlavička zápasu:** Detekce přes třídy `.alfa` (domácí), `.beta` (hosté) a `.delta` (skóre).
- **Mapování statistik:**
    - `B`, `BODY` -> `pts`
    - `2B` -> `fg2_made`
    - `3B` -> `fg3_made`
    - `TH` -> `ft_made`
    - `F-`, `CH` -> `fouls`
    - `MIN` -> `minutes`
    - `+/-` -> `plus_minus`
    - `DOS` -> `rebounds`
    - `AS` -> `assists`

---

## 3. Odolnost a varování
Extraktory jsou navrženy tak, aby při chybě (např. změna struktury webu) neshodily celý proces importu. Pokud pole není nalezeno, je přidáno varování do `metadata->warnings` a import pokračuje.

### Příklad varování:
- `Table not found`: Tabulka se zadaným selektorem nebyla nalezena.
- `Could not parse date`: Formát data neodpovídá očekávání.
- `Player ID not found`: Hráč nemá v tabulce odkaz na svůj profil (nelze ho v DB spárovat automaticky).

---

## 4. Testování
Extraktory jsou testovány v `Tests\Feature\Stats\Extractors\CzBasketballExtractorTest` s využitím statických HTML fixtur uložených v `tests/Fixtures/Stats/CzBasketball/`. Tím je zajištěna stabilita parserů i bez dostupnosti externího webu během běhu testů.
