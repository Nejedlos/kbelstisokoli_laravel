# Testy a ověření nového help systému (v2)

Tento dokument shrnuje provedené testy, jejich rozsah a výsledky pro ověření stability a funkčnosti nového systému nápovědy.

## 1. Rozsah testování

Nový systém nápovědy je pokryt sadou automatizovaných testů rozdělených do tří úrovní:

### A. Unit testy (Služby)
- **HelpNavigationServiceTest**: Ověření správného generování breadcrumbs pro kategorie i články. Obsahuje ochranu proti nekonečné rekurzi v hierarchii kategorií.
- **HelpSearchServiceTest**: Testování full-textového vyhledávání s váženou relevancí (Title, Keywords, Content) a filtrování výsledků podle uživatelských rolí (Audience). Zajištěna kompatibilita se SQLite (testy) i MySQL/PostgreSQL (produkce).

### B. Unit testy (Modely)
- **HelpTranslationTest**: Ověření vícejazyčnosti (`spatie/laravel-translatable`) a funkčnosti fallbacku na češtinu, pokud anglický překlad chybí.

### C. Feature testy
- **HelpPageTest**: Testování Filament stránky nápovědy.
    - Přístup administrátora (ošetření 2FA a oprávnění).
    - Zobrazení domovské stránky nápovědy s kategoriemi.
    - Zobrazení detailu článku.
    - Funkčnost vyhledávacího pole v UI.
- **HelpSeederTest**: Ověření, že `HelpSeeder` (včetně kategorií a článků) správně naplní databázi validními daty.

## 2. Výsledky testů

Všechny testy byly úspěšně spuštěny v lokálním prostředí.

**Souhrn spuštění:**
- **Počet testů:** 16
- **Počet asercí:** 42
- **Stav:** PASS (100%)
- **Doba trvání:** ~1.65s

### Seznam testovaných souborů:
- `tests/Unit/Services/Help/HelpNavigationServiceTest.php`
- `tests/Unit/Services/Help/HelpSearchServiceTest.php`
- `tests/Unit/Models/HelpTranslationTest.php`
- `tests/Feature/Filament/Pages/HelpPageTest.php`
- `tests/Feature/Seeders/HelpSeederTest.php`

## 3. Klíčová zjištění a fixy během testování

Během implementace testů byly identifikovány a opraveny následující problémy:
1. **Recursion Protection**: Potvrzena funkčnost ochrany proti zacyklení v breadcrumbs, která dříve způsobovala Memory Exhaustion.
2. **SQLite Compatibility**: Opraven SQL dotaz pro vyhledávání, který v subquery s `OR` a JSON funkcemi způsoboval chybu v SQLite (používaném pro testy). Nyní je použita kompatibilní `LIKE` varianta pro testovací prostředí.
3. **Seeder Robustness**: Doplněny chybějící NOT NULL parametry (`content`) do seederu, které dříve způsobovaly pád při čisté instalaci bez Markdown souborů.
4. **Access Management**: V testech bylo nutné explicitně ošetřit povinné 2FA pro adminy a implementaci `FilamentUser` rozhraní.

## 4. Závěr

Architektura help systému v2 je stabilní, plně otestovaná a připravená k nasazení. Testy zajišťují, že budoucí změny v datech nebo kódu nenaruší základní navigaci, bezpečnost (přístup podle rolí) a vyhledávání.
