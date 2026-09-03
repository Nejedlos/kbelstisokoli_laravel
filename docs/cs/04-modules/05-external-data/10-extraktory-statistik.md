# Extraktory statistik (CzBasketball)

Tento dokument popisuje fungování a strategii extrakce dat z webu `cz.basketball` pro projekt Kbelští sokoli.

## 1. Technický popis
Pro extrakci dat používáme Symfony Crawler. Hlavním cílem je získat provázaná data mezi zápasy, hráči a týmy.

### Klíčové komponenty:
- `MatchDetailBoxscoreExtractor`: Extrahuje detail zápasu (skóre, čtvrtiny, rozhodčí, boxscore).
- `TeamRosterExtractor`: Extrahuje soupisku týmu pro danou sezónu.
- `PlayerDetailExtractor`: Extrahuje profil hráče (výška, ročník, kariéra).

## 2. Identifikace entit
Vždy se snažíme používat `external_id` z URL adres:
- Hráč: `/hrac/116175` -> ID `116175`
- Zápas: `/zapas/518477` -> ID `518477`
- Tým: `/tym/7761` -> ID `7761`

Tyto ID ukládáme do metadat v databázi pro pozdější synchronizaci a párování.

## 3. Synchronizace a Ghost uživatelé
Při synchronizaci boxscoru (`StatisticSyncService`):
1. Pokud hráč v našem týmu není nalezen v DB, ale má `external_id`, automaticky se vytvoří "ghost" uživatel (`is_ghost = true`).
2. Tím zajistíme, že statistiky jsou vždy navázány na unikátní entitu, i když hráč ještě nemá v systému profil.
3. Pro soupeře ukládáme `row_label` (jméno) a statistiky bez vytváření uživatelů (pokud nejsou potřeba).

## 4. Testovací data (Fixtures)
Pro účely vývoje a testování jsou v adresáři `tests/fixtures/cz_basketball/` uloženy reálné HTML soubory:
- `team_2025.html`: Soupiska Kbelů pro sezónu 2024/25.
- `match_518477.html`: Detail zápasu se skóre a boxscorem.
- `player_116175.html`: Profil hráče Jakub Bartůněk.

Při stahování nových fixture dat je nutné používat platný `User-Agent`, jinak web `cz.basketball` může vrátit nekompletní obsah.

## 5. UI a zobrazení
V členské sekci se v detailu zápasu zobrazují:
- Skóre po čtvrtinách (vypočítané z průběžných stavů).
- Kompletní boxscore obou týmů.
- Rozšířené statistiky: Doskoky (REB), Asistence (AST), Zisky (STL), Ztráty (TOV), Bloky (BLK).
- Validita dat (VAL) s barevným odlišením výkonu.

## Regresní opravy a offline testy (3. 9. 2026)
Aktuální testovací podklady jsou syntetické HTML soubory v `tests/Fixtures/Stats/CzBasketball/` a `tests/Fixtures/Stats/Legacy/`; jsou verzované výjimkou z obecného zákazu `*.html`. Testy nesmějí vyžadovat místní historické soubory ani živou síť.

Pokud zápas ještě nemá boxscore, extraktor vrací prázdné řádky, metadata hlavičky a upozornění; metadata se nesmějí vydávat za řádky statistik. DOM extraktory procházejí sourozence pomocí `nextAll()` a ošetřují chybějící obalový div. Plánovač detailů váže aktuální čas jako SQL parametr, aby fungoval stejně s produkční databází i SQLite v testech.
