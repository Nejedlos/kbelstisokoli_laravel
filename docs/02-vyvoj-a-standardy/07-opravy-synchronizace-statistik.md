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

### Dopad
- Zápasy, které dříve vykazovaly skóre `- : -` nebo měly nesprávné údaje o čtvrtinách, jsou nyní synchronizovány správně.
- Zlepšená odolnost vůči drobným změnám v HTML struktuře hlavičky zápasu na webu `cz.basketball`.
