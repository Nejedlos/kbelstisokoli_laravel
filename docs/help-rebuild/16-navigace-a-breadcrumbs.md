# Navigace a Breadcrumbs v Help systému

Tento dokument popisuje implementaci navigační logiky v novém help systému, která zajišťuje plynulý průchod obsahem a snadnou orientaci uživatele.

## 1. Breadcrumbs (Drobečková navigace)

Breadcrumbs jsou generovány dynamicky pomocí `HelpNavigationService`.

- **Home:** Vždy první prvek, odkazuje na úvodní stránku nápovědy.
- **Kategorie:** Rekurzivně se skládají všechny nadřazené kategorie až k aktuálnímu článku/sekci.
- **Článek:** Poslední (aktivní) prvek v navigaci.

### Technická implementace
- Třída: `App\Services\Help\HelpNavigationService`
- Komponenta: `resources/views/components/help/breadcrumbs.blade.php`

## 2. Navigace mezi články (Next / Prev)

Každý detail článku obsahuje v dolní části navigaci na předchozí a následující článek v rámci téže kategorie.

- **Řazení:** Navigace respektuje pole `sort_order` a následně `id`.
- **Filtrování:** Respektuje stav publikace a role uživatele (uživatel neuvidí v navigaci článek, na který nemá oprávnění).
- **Vizuál:** Zobrazuje název článku a krátký úvod (pokud je k dispozici v metadatech).

### Technická implementace
- Logika: `HelpQueryService::getArticleNavigation()`
- Komponenta: `resources/views/components/help/article-navigation.blade.php`

## 3. Sidebar Navigace (Struktura kategorií)

V pravém sidebaru (na desktopu) nebo pod obsahem (na mobilu) je k dispozici kompletní strom kategorií.

- **Aktivní stavy:** Automaticky zvýrazňuje aktuální kategorii a aktuální článek.
- **Hierarchie:** Podporuje vnořené podkategorie a jejich články.

### Technická implementace
- Komponenta: `resources/views/components/help/sidebar-nav.blade.php`

## 4. Související obsah

Systém podporuje dva typy souvisejícího obsahu:

1. **Související články:** Definované ručně v databázi (pivot `help_article_related`). Zobrazují se v sidebaru jako "Související články".
2. **Rychlé akce:** Odkazy na reálné části administrace (např. "Přejít na správu členů"), které přímo souvisejí s tématem článku.

## 5. Navigační konzistence

Navigační parametry jsou sjednoceny napříč celým systémem:
- `?cat={slug}` - Zobrazení kategorie.
- `?file={slug}` - Zobrazení detailu článku.
- `?q={query}` - Vyhledávání.

Díky využití `HelpService` jako jednotného entrypointu je zaručeno, že se data (včetně navigace a breadcrumbs) načítají konzistentně bez ohledu na to, zda uživatel přišel z vyhledávání, home page nebo přímého odkazu.
