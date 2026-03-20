# Fix: Prázdné tabulky soutěží pro sezónu 2025/2026

## Problém
Uživatel nahlásil, že se na produkci nestahují data pro současnou sezónu a tabulky zůstávají prázdné.

## Příčina
1. **Změna na webu cz.basketball:** Na stránce týmu pro sezónu 2025/2026 (např. `https://cz.basketball/tym/7761?y=2025`) již není název soutěže uveden jako odkaz `<a>`, ale pouze jako prostý text v `<div>`.
2. **Extractor:** `TeamHeaderExtractor` hledal URL soutěže pouze v odkazech. Pokud odkaz nenašel, zůstala `competition_url` v konfiguraci `ExternalTeamSeasonConfig` prázdná.
3. **Služba:** `CompetitionSyncService` se spouští pouze v případě, že je `competition_url` vyplněna.

## Vyřešení
1. **Manuální oprava dat:** Do produkční databáze byly ručně doplněny správné URL soutěží pro aktivní týmy sezóny 3 (2025/2026):
    - Sokol Kbely C (7761) -> `https://cz.basketball/soutez/4993` (Přebor B)
    - Sokol Kbely E (7738) -> `https://cz.basketball/soutez/4999` (3.třída B)
2. **Úprava kódu (Robustnost):** `TeamHeaderExtractor.php` byl upraven tak, aby:
    - Vždy extrahoval název soutěže (i z textového `div`), i když chybí odkaz.
    - Byl odolnější proti chybám při procházení DOM (přidány kontroly na existenci nodů).
3. **Synchronizace:** Po opravě byl spuštěn příkaz `php artisan stats:import --force`, který úspěšně stáhl tabulky (21 záznamů v `CompetitionStanding` pro sezónu 3).

## Doporučení pro budoucno
Pokud se situace bude opakovat u nových sezón, je nutné:
1. Prověřit, zda `cz.basketball` opět nezměnil strukturu.
2. Případně ručně dohledat ID soutěže (např. přes detail zápasu) a doplnit do administrace v sekci **Externí zdroje -> Konfigurace sezón**.
