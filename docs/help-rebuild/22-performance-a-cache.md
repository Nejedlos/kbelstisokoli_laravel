# Výkon a cachování help systému

Tento dokument popisuje optimalizace a strategii cachování implementovanou v systému nápovědy (v2) pro zajištění bleskové odezvy i při velkém množství obsahu.

## 1. Databázové optimalizace

Pro rychlé vyhledávání a filtrování byly do databáze přidány indexy na klíčová pole:

- `help_categories`: `slug` (unique), `parent_id`, `sort_order`, `is_active`.
- `help_articles`: `slug` (unique), `category_id`, `sort_order`, `is_published`, `is_featured`.

## 2. Strategie cachování

Systém využívá `Cache::remember` pro minimalizaci DB dotazů. Klíče jsou dynamicky generovány na základě:
- Aktuálních rolí uživatele (hash rolí).
- Aktuálního jazyka (locale).
- Identifikátoru (slug nebo ID).

### Přehled cache klíčů:
- `help_home_categories_{roles_hash}_{locale}`: Seznam kategorií na úvodní stránce.
- `help_featured_articles_{roles_hash}_{locale}`: Seznam doporučených článků.
- `help_category_{slug}_{roles_hash}_{locale}`: Detail kategorie včetně článků.
- `help_article_{slug}_{roles_hash}_{locale}`: Detail článku včetně FAQ a rychlých akcí.
- `help_category_tree_{roles_hash}_{locale}`: Celý strom navigace (sidebar).
- `help_breadcrumbs_{type}_{id}_{locale}`: Cesta nápovědy.
- `help_search_{roles_hash}_{locale}_{query_hash}_{limit}`: Výsledky vyhledávání (TTL 1 hodina).

Všechny ostatní cache mají TTL **24 hodin**, ale jsou automaticky invalidovány při změně obsahu.

## 3. Eager Loading a Query Builder

Pro maximální výkon a eliminaci N+1 dotazů:
- **Query Builder**: Používán pro hromadné operace (seznamy, strom), kde není potřeba plná funkcionalita Eloquentu.
- **Zhloupnutí objektů**: Data z databáze jsou v rámci služeb předzpracována (dekódování JSON, překlady) a uložena jako jednoduché objekty, aby Blade šablony nemusely volat magické metody Laravelu nebo překladové traity.
- **Hromadné načítání**: Strom kategorií načítá všechny články jedním dotazem a rozděluje je v paměti.

## 4. Automatická invalidace (Cache Purge)

Díky registraci v `AppServiceProvider` modely nápovědy používají `PerformanceObserver`. 

Při jakékoli změně (`saved`, `deleted`) v modelech:
- `HelpCategory`
- `HelpArticle`
- `HelpFaq`
- `HelpQuickAction`

Dojde k automatickému promazání cache nápovědy. V prostředí s databázovým cache driverem jsou cíleně smazány klíče začínající na `help_`. Na jiných driverech (File) se postupuje podle globální politiky projektu pro daný hosting.

## 5. Optimalizace vyhledávání

Vyhledávání využívá vážený relevance ranking přímo v SQL:
1. **Shoda v titulku**: Váha 10.
2. **Shoda v klíčových slovech**: Váha 8.
3. **Shoda v účelu článku**: Váha 5.
4. **Shoda v obsahu**: Váha 3.

Výsledky jsou cachovány, aby se předešlo opakovanému náročnému vyhodnocování stejných dotazů.
