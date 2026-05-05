# Oprava mizejících nebo chybějících výsledků zápasů (5. 5. 2026)

## Problém
Uživatel hlásil, že u odehraných zápasů v členské sekci chybí výsledky (skóre), i když statistiky (grafy) jsou viditelné. V tabulkách byl pouze text "akce proběhla" (status finished), ale skóre bylo prázdné.

## Příčina
1. **Deduplikace zápasů:** Při sloučení duplicitních záznamů (např. po změně termínu nebo ID zápasu ve zdroji) se sice sjednotily statistiky, ale pokud primární záznam neměl skóre a smazaný duplikát ano, skóre se nepřevedlo.
2. **Synchronizace seznamu (List):** Kód pro synchronizaci seznamu zápasů mohl v určitých případech přepsat existující skóre hodnotou NULL, pokud externí zdroj v danou chvíli skóre neposkytl (zejména u zápasů, které měly dříve status "played" a ne "finished").
3. **Interval synchronizace:** Zápasy, které sice měly marker o proběhlé synchronizaci statistik, ale chybělo jim skóre v DB, byly blokovány 24hodinovým intervalem.

## Vyřešení
- **MatchSyncService:**
    - Upravena metoda `mergeDuplicatesForMatch` – nyní při sloučení přenáší `score_home`, `score_away` a status `finished` na primární záznam, pokud mu chybí.
    - Metoda `sync` byla posílena o ochranu existujícího skóre a statusu. Pokud je zápas v DB již `finished` (nebo `played`/`completed`), nenechá se přepsat stavem `planned` ze seznamu.
- **ExternalStatsSyncService:**
    - Zvýšen limit pro detailní synchronizaci zápasů v jedné dávce z 15 na 50.
    - Implementována prioritizace: zápasy v minulosti, které nemají vyplněné skóre v DB, se řadí na začátek fronty.
    - Upraven interval: pokud zápasu v aktuální sezóně chybí skóre, zkouší se synchronizace každou hodinu (místo 24h), i když už má příznak o synchronizaci statistik.
- **Data:** Provedena plná synchronizace týmů `muzi-c` a `muzi-e`, čímž se doplnilo skóre u cca 40 zápasů v sezóně 2025/2026.

## Ověření
- Tinker na produkci potvrdil, že počet zápasů bez skóre v sezóně 3 klesl ze 46 na 4 (zůstaly pouze neoficiální turnaje).
- Konkrétní zápas 2241 (Sokol Kbely C) má nyní v DB skóre 63:76 a status `finished`.
- Cache statistik byla promazána.
