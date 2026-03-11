# Opravy a stabilizace Help systému (v2)

Tento dokument zaznamenává opravy kritických chyb nalezených po nasazení nové verze help systému.

## 1. Oprava chyby 500 (Allowed memory size exhausted)
- **Příčina:** Nekonečná smyčka `while ($current)` v `HelpNavigationService::addCategoryBreadcrumbs()`. Při existenci cyklické vazby v kategoriích (např. kategorie je sama sobě rodičem) docházelo k vyčerpání paměti PHP.
- **Řešení:** 
    - Do smyčky přidána kontrola navštívených ID (`$visited`).
    - Přidán bezpečnostní limit na hloubku zanoření (max 10 úrovní).
    - Pokud je detekován cyklus nebo dosažen limit, smyčka se ukončí.

## 2. Oprava chyby (Undefined array key "url")
- **Příčina:** Komponenta `x-help.breadcrumbs` očekávala v poli breadcrumbů klíč `url`, který `HelpNavigationService` negeneroval (přidával pouze `slug`).
- **Řešení:** 
    - Metoda `getBreadcrumbs()` byla upravena tak, aby pro každý krok cesty (Home, Kategorie, Článek) generovala absolutní URL pomocí `\App\Filament\Pages\Help::getUrl()`.

## 3. Sjednocení pojmenování polí (name vs. title)
- **Problém:** Model `HelpCategory` používá v databázi pole `name`, ale model `HelpArticle` používá `title`. V blade šablonách byl pro kategorie nekonzistentně používán atribut `title` (`$category->title`), což mohlo vést k chybám.
- **Řešení:** 
    - Do modelu `HelpCategory` byl přidán virtuální getter `getTitleAttribute()`, který vrací hodnotu z pole `name`.
    - Tím je zajištěna kompatibilita se stávajícími šablonami a konzistence s modelem `HelpArticle`.

## 4. Ochrana stability
- Veškeré přístupy k vlastnostem kategorií v navigaci byly ověřeny a sjednoceny na používání `title` (přes getter).
