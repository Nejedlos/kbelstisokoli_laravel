# Oprava mizení statistik zápasů (5. 5. 2026)

## Problém
Uživatel nahlásil, že ze stránek (veřejných i členské sekce) zmizely statistiky odehraných zápasů a jejich výsledky, přestože byly dříve úspěšně nasynchronizovány.

## Analýza
Při zkoumání databáze na produkci bylo zjištěno:
1. Tabulka `statistic_rows` obsahovala tisíce záznamů (data tedy v DB byla).
2. Tabulka `basketball_matches` obsahovala u mnoha zápasů (cca 120 celkem, 39 v aktuální sezóně) `score_home = null` a `score_away = null`, přestože měly přiřazené statistiky.
3. Livewire komponenty a servisní vrstvy filtrují zobrazení statistik pouze pro zápasy se statusem `finished` a nenulovým skóre.

**Příčina:**
V souboru `MatchSyncService.php` v metodě `sync` docházelo k bezpodmínečnému přepisování skóre a statusu daty získanými ze seznamu zápasů (list). Pokud externí web (cz.basketball) v tomto seznamu dočasně neměl uvedeno skóre (např. chyba scraperu nebo dočasná nedostupnost detailu v přehledu), service přepsala stávající správné skóre v DB hodnotou `null`. Status se pak přepnul z `finished` na `planned`, což skrylo statistiky v UI.

## Řešení
1. **Úprava kódu:** V `MatchSyncService::sync` byla přidána ochrana. Nyní se existující skóre a status `finished` v DB považují za prioritní a nejsou přemazány prázdnými hodnotami z listu (pokud není vynucen fresh mód).
2. **Obnova dat:** Byla spuštěna plná synchronizace všech týmů aktuální sezóny s příznakem `--force`, která obnovila chybějící skóre z externích zdrojů.
3. **Validace:** Tinker dotaz potvrdil, že v sezóně 2025/26 již neexistují zápasy se statistikami bez skóre.

## Nasazení
- Soubor `app/Services/Stats/Sync/MatchSyncService.php` byl aktualizován na produkci.
- Proběhl `php artisan optimize:clear` pro promazání cache statistik.
- Statistiky jsou nyní opět viditelné.
