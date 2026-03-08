# Statistiky a soutěže

Účel: Správa a zobrazení sportovních statistik a interních klubových soutěží.

## 1. Datový model
- **StatisticSet:** Definice tabulky (např. "Ligová tabulka"). Obsahuje `column_config` (JSON) pro dynamické sloupce.
- **StatisticRow:** Jednotlivé řádky dat. Hodnoty jsou uloženy v JSON poli `values`. Vazba na `player`, `team`, `match` nebo `season`.
- **ExternalStatSource:** Konfigurace pro budoucí automatizované importy z externích URL.
- **ClubCompetition:** Interní soutěže (např. Lumír Trophy).
- **ClubCompetitionEntry:** Jednotlivé zápisy do soutěže (body, asistence).

## 2. AI Ingest Pipeline
Architektura je připravena na automatizovanou extrakci dat pomocí AI:
1. **Fetcher:** Stáhne HTML obsah z URL externí asociace.
2. **Extractor:** Vyhledá relevantní tabulku v HTML.
3. **Normalizer (AI):** LLM transformuje surová HTML data do standardizovaného DTO formátu.
4. **Importer:** Uloží data do příslušného `StatisticSet`.

Všechny tyto části mají definované rozhraní (Interfaces) v `app/Services/Stats/Contracts`.

## 3. Administrace (Filament)
- **Dynamické formuláře:** `RowsRelationManager` automaticky generuje vstupní pole podle toho, jaké sloupce admin v sadě statistik definoval.
- **Leaderboardy:** Soutěže zobrazují automaticky seřazené pořadí účastníků.
- **Oprávnění:** `manage_stats` (oficiální data), `manage_competitions` (klubové soutěže).

## 4. Zobrazení (Frontend)
- **Blok `stats_table`:** Umožňuje vložit libovolnou tabulku statistik do CMS stránky.
- **Komponenta `<x-leaderboard />`:** Pro vizuální zobrazení pořadí v klubových soutěžích.
