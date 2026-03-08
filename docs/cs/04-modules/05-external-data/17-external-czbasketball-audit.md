# Audit externího zdroje cz.basketball

Tento dokument obsahuje technickou mapu a analýzu struktury webu cz.basketball pro účely automatizovaného importu statistik.

## 1. Struktura URL a stabilní identifikátory

Všechny klíčové entity používají v URL číselná ID, která jsou stabilní a lze je použít jako `external_id` v naší databázi.

| Entita | URL vzor | Příklad |
| :--- | :--- | :--- |
| **Tým** | `https://cz.basketball/tym/{teamId}?y={year}` | `https://cz.basketball/tym/7738?y=2025` |
| **Zápas** | `https://cz.basketball/zapas/{matchId}` | `https://cz.basketball/zapas/519196` |
| **Hráč** | `https://cz.basketball/hrac/{playerId}` | `https://cz.basketball/hrac/11246` |
| **Seznam zápasů** | `https://smo.cz.basketball/zapasy?c={teamId}&y={year}` | `https://smo.cz.basketball/zapasy?c=7738&y=2025` |

*Poznámka: Parametr `y` (rok) označuje začátek sezóny (např. `2025` pro sezónu 2025/26).*

## 2. Mapování obsahu pro import

### 2.1 Soupiska týmu (Roster)
- **Zdroj:** Detail týmu, záložka "soupiska".
- **Selektor:** `table.js-table-fixed-order`
- **Tělo tabulky:** `tbody#pagination-wrapper-01`
- **Klíčová data:**
    - Jméno hráče a ID (z odkazu `/hrac/{id}`).
    - Rok narození, výška (pro doplnění profilu).
    - Základní souhrnné statistiky sezóny.

### 2.2 Seznam zápasů (Schedule & Results)
- **Zdroj:** Stránka `smo.cz.basketball/zapasy`.
- **Selektor:** `table.table-striped`
- **Klíčová data:**
    - Kolo, Datum a čas (textová transformace na DateTime).
    - Domácí / Hosté (názvy týmů).
    - Skóre (pokud je odehráno).
    - Odkaz na detail zápasu (`/zapas/{id}`).

### 2.3 Statistiky zápasu (Boxscore)
- **Zdroj:** Detail zápasu.
- **Selektor:** `table.table-condensed.table-bordered`
- **Struktura:** V detailu jsou obvykle dvě tyto tabulky (jedna pro domácí, jedna pro hosty). Jsou umístěny v kontejnerech pod nadpisy `h4` s názvem týmu.
- **Klíčová data:**
    - Číslo dresu.
    - ID hráče (z odkazu).
    - Statistiky: 2B (body/pokusy), 3B, TH, Chyby, Body celkem, +/-.

### 2.4 Profil hráče
- **Zdroj:** Detail hráče.
- **Selektor:** Tabulky v záložkách `tab-pane-two` (všechny zápasy) a `tab-pane-three` (sezónní souhrny).
- **Využití:** Primárně jako doplňkový zdroj nebo pro verifikaci konzistence dat.

## 3. Implementační strategie

### 3.1 Parser
- **Primární:** `Symfony\Component\DomCrawler\Crawler` a `XPath`.
- **Důvod:** Struktura webu je relativně stabilní, používá sémantické tabulky a unikátní ID/třídy u klíčových prvků.
- **Zpracování tabulek:** Iterace přes řádky `<tr>` a extrakce textu/atributů z `<td>`.

### 3.2 Normalizace dat
- **Datum:** Převod z českého formátu (např. `6. 3. 2026 Pá 19:15`) na `Y-m-d H:i:s`.
- **Párování:** 
    - Hráči: Přes `external_id` (z URL) nebo `license_number` (pokud je dostupné v HTML jako text).
    - Týmy: Přes `external_id` v URL týmu.
- **AI Fallback:** Pokud `DomCrawler` narazí na neočekávanou změnu struktury (např. chybějící sloupce), fragment HTML bude předán AI Normalizeru k extrakci klíč-hodnota.

## 4. Konkrétní XPath selektory

| Prvek | XPath / CSS Selektor |
| :--- | :--- |
| **Tabulka soupisky** | `table.js-table-fixed-order` |
| **Odkazy na hráče** | `//a[contains(@href, '/hrac/')]` |
| **Tabulka zápasů** | `table.table-striped` |
| **Odkazy na zápasy** | `//a[contains(@href, '/zapas/')]` |
| **Boxscore tabulky** | `//h4[contains(text(), 'NÁZEV TÝMU')]/following-sibling::div//table` |

---
*Zpracoval: Junie (AI Developer)*
*Datum: 2026-03-03*
