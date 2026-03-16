# Opravy synchronizace statistik (cz.basketball)

Tento dokument zaznamenává opravy a vylepšení v procesu synchronizace statistik z externího zdroje `cz.basketball`.

## 1. Stabilizace parsování skóre a čtvrtin (Březen 2026)

### Problém
- **Nespolehlivé parsování skóre:** Skóre v hlavičce detailu zápasu bylo často obaleno v závorkách a obsahovalo bílé znaky (např. `( 39:78 )`), což způsobovalo selhání původního regexu.
- **Kumulativní skóre čtvrtin:** Na `cz.basketball` jsou stavy čtvrtin často uváděny jako průběžné skóre (např. 9:17, 22:34, 31:54). Původní kód tyto hodnoty bral jako body v dané čtvrtině, což vedlo k nesmyslným statistikám.
- **Chybějící poslední čtvrtina:** V hlavičce detailu zápasu často chybí poslední čtvrtina (je-li shodná s celkovým skóre), což vedlo k neúplným datům o průběhu zápasu.

### Řešení (`MatchDetailBoxscoreExtractor.php`)
- **Robustní regex pro skóre:** Upraven regex `/(?<![\d:])(\d{1,3})\s*:\s*(\d{1,3})(?![\d:])/u`, který nyní ignoruje okolní znaky (závorky) a bílé znaky uvnitř skóre.
- **Detekce a přepočet kumulativního skóre:** Implementována logika, která detekuje, zda jsou stavy čtvrtin kumulativní (což je na tomto zdroji standard) a automaticky je přepočítává na body v jednotlivých čtvrtinách (odečítáním předchozího stavu).
- **Automatické doplnění poslední čtvrtiny:** Pokud po parsování čtvrtin z hlavičky nesouhlasí poslední stav s celkovým skóre, je automaticky doplněna chybějící perioda (obvykle 4. čtvrtina) dopočtem z celkového výsledku.
- **Vylepšená detekce týmů:** Upravena logika pro rozpoznávání názvů týmů v hlavičce zápasu, která nyní správně odlišuje skóre od jmen týmů, i když mají stejné CSS třídy (např. `.alfa`).
- **Generování textového popisu čtvrtin:** Pokud v hlavičce chybí textový rozpis čtvrtin, systém jej nyní automaticky generuje z extrahovaných dat, aby byl zobrazen v přehledu zápasů.

### Dopad
- Zápasy, které dříve vykazovaly skóre `- : -` nebo měly nesprávné údaje o čtvrtinách, jsou nyní synchronizovány správně.
- Zlepšená odolnost vůči drobným změnám v HTML struktuře hlavičky zápasu na webu `cz.basketball`.

## 2. Stabilizace hromadné synchronizace hráčů a skiping (Březen 2026)

### Problém
- **Zasekávání na hráčích:** Při hromadné synchronizaci (zejména "excesivní" hloubkové historii) se proces občas zasekl na jednom hráči (např. kvůli timeoutu externího serveru nebo nedostupnosti URL).
- **Restartování od začátku:** Původní `SyncPlayersJob` začínal vždy od prvního hráče v DB, což při nutnosti restartu znamenalo, že se proces nikdy nedokončil, pokud se zasekl v polovině.
- **Nemožnost přeskočení:** Uživatel neměl možnost "přeskočit" aktuálního problematického hráče a pokračovat na dalšího bez zrušení celého procesu.

### Řešení
- **Optimalizace timeoutů (`CzBasketballFetcher.php`):** Snížen timeout na 15s a počet pokusů (retry) na 2 pro rychlejší detekci nedostupnosti a zkrácení doby "visení".
- **Zavedení Batch Runu (`SyncPlayersJob.php`):** Hromadná synchronizace nyní vytváří hlavní `ExternalImportRun`, který zastřešuje všechny hráče. Uživatel tak v UI vidí celkový progres (např. 15/120 hráčů).
- **Inteligentní řazení:** Job nyní řadí hráče podle `updated_at` v jejich profilu (nejstarší první). Při restartu se tak automaticky pokračuje těmi, kteří nebyli dlouho synchronizováni.
- **Funkce "Přeskočit" (Skip) v UI:** V `SyncStatusBar` přibylo tlačítko "Přeskočit" pro aktivní synchronizaci hráče. To umožní uživateli manuálně skipnout problematického jedince a job automaticky pokračuje na dalšího hráče v seznamu.
- **Kontrola statusu ve smyčkách:** Do `PlayerSyncService.php` a `ExternalStatsSyncService.php` doplněny častější kontroly statusu běhu (včetně kontroly po každém síťovém požadavku), což zajišťuje okamžitou reakci na "Zrušit" nebo "Přeskočit".
- **Stabilizace týmové synchronizace:** Podobné principy aplikovány i na hromadnou synchronizaci týmů/sezón (`stats:sync-team-season`), aby bylo možné přeskočit visící synchronizaci soupisky nebo seznamu zápasů.

### Dopad
- Hromadná synchronizace je nyní mnohem robustnější a dojde do konce i při občasných výpadcích externího zdroje.
- Uživatel má lepší kontrolu nad běžícími procesy díky vizualizaci celkového progresu a možnosti selektivního přeskakování.
- Snížení zátěže na frontu díky rychlejšímu odbavování neúspěšných požadavků.
