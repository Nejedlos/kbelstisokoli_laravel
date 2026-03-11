# Filament Help Page

Tento dokument popisuje implementaci nové stránky nápovědy ve Filamentu, která je napojena na databázový systém.

## Přehled
Nová stránka nápovědy (`App\Filament\Pages\Help`) nahrazuje původní Markdown prohlížeč. Je navržena jako single-page aplikace v rámci Filamentu, která využívá URL parametry pro navigaci mezi kategoriemi a články.

## Technická architektura

### Page Class (`App\Filament\Pages\Help`)
- **Využívá Livewire URL parametry:**
    - `currentCategory` (alias `cat`): Slug aktuálně vybrané kategorie.
    - `currentFile` (alias `file`): Slug aktuálně vybraného článku.
    - `searchQuery` (alias `q`): Vyhledávací dotaz.
- **Service Integration:** Veškerá data jsou získávána přes `HelpService`.
- **Audience Filtering:** Stránka automaticky detekuje role přihlášeného uživatele a předává je do service layer, která zajistí odfiltrování článků, na které uživatel nemá mít přístup.

### View (`resources/views/filament/pages/help.blade.php`)
View je rozděleno do tří hlavních stavů na základě URL parametrů:
1. **Landing Page:** Zobrazuje se, pokud není vybrána kategorie ani článek. Obsahuje vyhledávání, seznam hlavních kategorií a doporučené články.
2. **Category Listing:** Zobrazuje se, pokud je nastaven `cat`. Vypisuje všechny články v dané kategorii.
3. **Article Detail:** Zobrazuje se, pokud je nastaven `file`. Obsahuje samotný text článku (renderovaný z Markdownu), FAQ, rychlé akce a související články.

### Komponenty (`resources/views/components/help/`)
Pro udržení čistého kódu ve view byly vytvořeny dedikované Blade komponenty:
- `breadcrumbs`: Drobečková navigace.
- `category-card`: Karta kategorie na landing page.
- `article-card`: Karta článku v listingu nebo výsledcích hledání.
- `sidebar-nav`: Postranní navigace (strom kategorií).
- `audience-badge`: Štítek s rolí, pro kterou je článek určen.
- `callout`: Vizuální zvýraznění (Tip, Varování).
- `faq-item`: Rozbalovací položka FAQ.
- `quick-action`: Tlačítko pro rychlou akci v adminu.

## Routing a navigace
Stránka nepoužívá standardní Laravel routy pro články, ale využívá reaktivitu Livewire. To umožňuje:
- Okamžité přepínání obsahu bez reloadu celé stránky.
- Zachování funkčnosti tlačítka "Zpět" v prohlížeči díky synchronizaci s URL.
- Snadné sdílení odkazů na konkrétní články (např. `/admin/help?file=sprava-dochazky`).

## Lokalizace
Všechny texty v UI jsou lokalizovány pomocí standardních Laravel překladů v `lang/{locale}/admin.php`. Obsah článků a názvy kategorií jsou uloženy jako JSON v databázi a spravovány balíčkem `spatie/laravel-translatable`.

## Role a oprávnění
Viditelnost obsahu je řízena na úrovni `HelpQueryService` pomocí scopu `forAudience`. Pokud článek nemá definovány žádné role, je viditelný pro všechny. Pokud má definované role, uvidí ho pouze uživatelé, kteří mají alespoň jednu z těchto rolí.
