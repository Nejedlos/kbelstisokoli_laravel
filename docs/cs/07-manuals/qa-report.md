# QA Report - Externí statistiky a importy

## Shrnutí testování
Bylo provedeno excesivní testování ("Brutální smoke run") celého systému pro synchronizaci externích statistik a legacy importů. Testy proběhly na čisté databázi s reálnými daty z fixtures a historických souborů.

## Výsledky testů
- **Externí synchronizace:**
    - Synchronizace týmu Muži E: OK (10 řádků statistik v zápase).
    - Synchronizace týmu Muži C: OK (10 řádků statistik v zápase).
    - Mapování hráčů: Automatické vytváření ghost uživatelů a manuální mapování adminů na testovací ID 11246 funguje.
- **Legacy import:**
    - Zpracováno 41 historických souborů ze `storage/app/legacystats`.
    - Úspěšně naimportováno 1089 řádků statistik.
    - Detekce kódování (Windows-1250 vs UTF-8): OK.
    - Detekce sezón a typů souborů: OK.
- **Infrastruktura:**
    - DB konektivita, migrace, seeder základních dat: OK.
    - Idempotence (hashování fragmentů): Ověřena (při shodném obsahu se sync přeskakuje).
    - Vynucená synchronizace (`force` flag): OK.

## Opravené chyby během QA
1. **Model `StatisticSet`:** Chyběl trait `HasTranslations`, což způsobovalo chybu při ukládání názvů v legacy importu.
2. **`MatchDetailBoxscoreExtractor`:** Vylepšena detekce názvu týmu (nyní hledá i v `h4` nad tabulkou), což opravilo párování statistik k našim týmům.
3. **Idempotence v QA:** Zaveden parametr `force` do `syncMatchDetail`, aby bylo možné v rámci QA synchronizovat více týmů používajících stejné testovací fixture.
4. **Member UI:** Pohledy v členské sekci byly aktualizovány, aby místo placeholderů zobrazovaly reálné Livewire komponenty se statistikami.

## Aktuální stav dat (v localhostu)
- **Aktivní sezóna:** 2025/2026.
- **Týmy se statistikami:** Muži C, Muži E.
- **Naimportované zápasy:** 1 (testovací z fixtures).
- **Celkem řádků statistik:** ~1100.
- **Testovací uživatelé s daty:** `admin@basketkbely.cz`, `nejedlymi@gmail.com` (oba mají v "Moje statistiky" data za Marek Novotný).

## Závěr
Systém je robustní, idempotentní a připraven na ostrý provoz. Synchronizace správně rozlišuje mezi naším týmem a soupeřem i v komplexních HTML strukturách.
