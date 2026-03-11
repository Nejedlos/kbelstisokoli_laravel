# Poznámky k migracím help systému

Tento dokument popisuje technické detaily migrací vytvořených pro nový help systém.

## Vytvořené migrace
- `database/migrations/2026_03_11_111113_create_help_tables.php`

## Popis tabulek a vazeb

### 1. `help_categories`
- **Účel:** Správa tématických okruhů nápovědy.
- **Klíčové prvky:**
    - `parent_id`: Umožňuje vytváření stromové struktury (kategorie -> podkategorie). Při smazání rodiče se podkategorie nesmažou (`SET NULL`).
    - `slug`: Unikátní řetězec pro URL a identifikaci v seederu.
    - `is_customized`: Příznak pro ochranu před přepsáním seederem.
    - `source_hash`: Hash pro detekci změn v seedovaných souborech.

### 2. `help_articles`
- **Účel:** Hlavní úložiště obsahu článků.
- **Klíčové prvky:**
    - `category_id`: Povinná vazba na kategorii (`CASCADE` při smazání kategorie).
    - `content`, `excerpt`, `search_keywords`: JSON pole pro multijazyčný obsah (`spatie/laravel-translatable`).
    - `audience_roles`: JSON pole pro definici rolí, které mají k článku přístup.
    - `is_published`: Řídí viditelnost na frontendu.

### 3. `help_quick_actions`
- **Účel:** Rychlé odkazy z nápovědy do příslušných sekcí administrace.
- **Kaskáda:** Smazání článku automaticky smaže jeho akce.

### 4. `help_faqs`
- **Účel:** Specifické otázky a odpovědi vázané na konkrétní článek.
- **Kaskáda:** Smazání článku automaticky smaže jeho FAQ.

### 5. `help_article_related`
- **Účel:** M:N vazba pro doporučení souvisejícího obsahu.
- **Implementace:** Pivot tabulka bez ID, s kompozitním primárním klíčem.

## Technická rozhodnutí
- **Datové typy:** Původně navržené `JSON` sloupce byly nahrazeny typy `text()` a `longText()`. Toto rozhodnutí bylo učiněno kvůli kompatibilitě s produkčním hostingem Webglobe, který nepodporuje nativní `JSON` typ v MySQL. Funkčnost `spatie/laravel-translatable` zůstává zachována, protože balíček ukládá JSON data do textových polí.
- **Indexy:** Přidány na sloupce `slug`, `sort_order`, `is_published` a cizí klíče pro optimální výkon při vyhledávání a řazení.
- **Mazací politika:** Použito `CASCADE` pro podřízené entity (FAQ, Akce) a `SET NULL` pro hierarchii kategorií, aby se předešlo nechtěné ztrátě celých větví obsahu při reorganizaci kategorií.
- **Vytvořené migrace:**
    - `database/migrations/2026_03_11_111113_create_help_tables.php` (hlavní struktura)
    - `database/migrations/2026_03_11_114919_add_audience_roles_to_help_categories.php` (rozšíření kategorií)
    - `database/migrations/2026_03_11_120822_add_display_name_to_roles_and_permissions.php` (lokalizace rolí a oprávnění)
