# Administrace (Filament PHP)

Administrační rozhraní projektu je postaveno na **Filament PHP 5**. Tento framework poskytuje robustní základ pro správu dat, uživatelů a obsahu.

## Hlavní moduly
1. **Dashboard:** Přehled důležitých informací (statistiky, grafy, notifikace).
2. **Uživatelé:** Správa uživatelských účtů, členů klubu a oprávnění (Spatie Permissions).
3. **Ekonomika:** Správa plateb, příspěvků a finančních reportů.
4. **Obsah (CMS):** Správa webové prezentace (články, akce, galerie, bannery).

## Technické standardy a vývoj
Při vývoji administrace striktně dodržujeme následující pravidla:

### 1. Ikony a UI
- Používáme **Font Awesome 7 Pro** (varianta **Light**).
- **Sidebar (Navigace):** Ikony v sidebaru se definují přes aliasy v `app/Providers/Filament/AdminPanelProvider.php` (metoda `->icons()`). V Resource/Page pak vracíme pouze název aliasu (např. `return 'fa-light-users';`) v metodě `getNavigationIcon()`.
- **Formuláře a tabulky:** V ostatních částech (akce, záložky, sekce) vkládáme ikony pomocí `HtmlString`: `->icon(new HtmlString('<i class="fa-light fa-users"></i>'))`.

### 2. Lokalizace
- Administrace podporuje češtinu (`cs`) a angličtinu (`en`).
- Pro překlady polí v databázi používáme `spatie/laravel-translatable`.
- UI překlady jsou v `lang/*.json` nebo `lang/{locale}/*.php`.

### 3. Generátory a CLI (Non-interactive Workflow)
Aby se předešlo problémům v automatizovaném prostředí a zablokování terminálu, používáme pro generování prvků administrace výhradně neinteraktivní příkazy:

- **Generování Resource:**
  `php artisan make:filament-resource Product --generate --no-interaction`
- **Generování Relation Manageru:**
  `php artisan make:filament-relation-manager CategoryResource products name --no-interaction`

**Pravidlo:** Pokud generátor vyžaduje dodatečné informace, které nelze předat přes argumenty, použijeme `--no-interaction` s výchozími hodnotami a následně kód upravíme ručně v PHP třídě.

## Vlastní komponenty
Administrace využívá standardní Filament komponenty a také vlastní:
- **Slider:** Pro výběr číselných hodnot (implementován v `public/js/filament/forms/components/slider.js`).
- **Code Editor:** Pro technická nastavení a úpravu konfiguračních řetězců.

## Přístup
Administrace je dostupná na `/admin`. Přístup je řízen pomocí Laravel politik (Policies) a rolí (Spatie Permissions). Pouze uživatelé s rolí `admin` nebo `super-admin` mají přístup do celého systému.
