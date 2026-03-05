# AI CNH Clipping (cz.basketball)

Tento dokument popisuje „Clipped Normalized HTML“ (CNH) pipeline pro týmové stránky cz.basketball a navazující AI normalizaci.

## Cíl
Minimalizovat vstup pro AI (LLM) na přesně definované fragmenty stránky a tím:
- zlepšit úspěšnost parsování,
- omezit velikost promptu (< 80 KB),
- učinit proces deterministickým a snadno debugovatelným.

## Co CNH obsahuje
Sekce v pevně daném pořadí a s konzistentními ID:
1. `#team-header` – H1 název týmu + info bloky (Klub, Kategorie, Soutěž, volitelně základní informace)
2. `#tab-roster` – pouze tabulka soupisky s podpisem (signature headers):
   - Musí obsahovat hlavičky: Hráč, Rok narození, Min., TH %
   - A zároveň v těle tabulky alespoň 3 odkazy `/hrac/{id}`
3. `#tab-matches` – primární tabulka zápasů (A) s podpisem:
   - Hlavičky: Číslo utkání, Datum, Soupeř, Skóre, TH %
   - Alespoň 1 odkaz `/zapas/{id}`
   - „Sekundární“ tabulka (B) se ignoruje nebo jde do `#tab-stats`, aby nemátla AI
4. `#tab-stats` – volitelné „extra statistics“ tabulky (jen pokud mají ≥ 5 řádků)
5. `#tab-history` – tabulka historie s podpisem:
   - Hlavičky: Sezóna, Soutěž, Umístění, Počet bodů

> Pozn.: Pokud by velikost CNH přesáhla 80 KB, zahodí se nejprve `#tab-stats`, případně i `#tab-history`. Základ (header + roster + matches) je vždy zachován.

## Co je odstraněno
- Globální patička, navigace, partneři, cookie banery
- Všechny netabulkové layouty a tabulky bez podpisových hlaviček
- Všechny HTML atributy mimo `href`, `colspan`, `rowspan` (pro determinismus a malé HTML)

## Normalizace odkazů
Všechny relativní odkazy se převádějí na absolutní (`https://cz.basketball/...`). V rámci klipů se navíc vytváří JSON „extracted_links“:

```json
{
  "players": [{"id":96986, "url":"...", "name":"Samuel Lněníček"}],
  "matches": [{"id":518451, "url":"...", "name":"...", "number":"104"}],
  "opponent_teams": [{"id":7764, "url":"...", "name":"TJ ČSA"}],
  "competitions": [{"id":4625, "url":"...", "label":"Přebor B"}]
}
```

- `matches[].number` je odhadnuto z první buňky řádku, ve kterém se odkaz na `/zapas/` nachází (pokud existuje).

## Uložení CNH a odkazů
- CNH soubor z team page: `storage/app/external/czbasketball/clips/{teamId}/y{year}/team_page_cnh.html`
- JSON odkazy: `storage/app/external/czbasketball/clips/{teamId}/y{year}/extracted_links.json`
- Pro stránku se seznamem zápasů se ukládá `matches_list_links.json` do stejné složky.

## AI vstup (strict fragments)
AI nikdy nedostane celé CNH. Do LLM posíláme pouze:
- fragment soupisky (`<table>...</table>`),
- fragment zápasů (`<table>...</table>`),
- volitelně fragment historie,
- JSON `extracted_links` jako kontext pro verifikaci párování.

## Debug & Observability
- Do metadat běhu se ukládají: velikost původního HTML, velikost CNH, ID klipů, cesty k souborům.
- OpenAI normalizér zapisuje časová razítka (start, sanitizace, odeslání, odpověď) a délky promptů.

## Testy
- Unit: `TeamPageClipperTest` pokrývá detekci všech tří hlavních tabulek, extrakci odkazů a limit velikosti CNH.
- Determinismus: opakovaný build CNH vrací identický obsah (kontrolováno hashováním).

## Změněné soubory
- `app/Services/Stats/Clippers/CzBasketball/CzBasketballTeamPageClipper.php` – heuristiky, sanitizace, absolutizace, CNH builder, odkazy
- `app/Services/Stats/Sync/ExternalStatsSyncService.php` – uložení CNH/links, předání `context_links` do AI
- `app/Services/Stats/Normalizers/OpenAiNormalizer.php` – podpora `context_links` v promptu
- `tests/Unit/.../TeamPageClipperTest.php` – unit testy

## Důvod proč to funguje lépe
- AI vidí jen jednu pravou tabulku zápasů (A) a minimalizovaný, deterministický HTML
- Kontextové odkazy pomáhají verifikovat a doplnit chybějící informace bez halucinací
