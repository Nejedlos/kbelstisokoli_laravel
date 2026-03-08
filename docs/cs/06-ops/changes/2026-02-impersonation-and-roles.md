# Impersonace a Správa rolí (Únor 2026)

Tento modul implementuje možnost pro administrátory vystupovat jako jiný uživatel (impersonace) a definuje novou strukturu rolí a oprávnění.

## 1. Impersonace (User Impersonation)
Funkce umožňuje superadminovi dočasně se přihlásit jako jakýkoliv jiný uživatel bez znalosti jeho hesla.

### Technické řešení
- **Kontroler:** `App\Http\Controllers\Admin\ImpersonateController`
- **Routy:**
    - `admin.impersonate.start` (`/admin/impersonate/{userId}`) - Spustí impersonaci.
    - `admin.impersonate.stop` (`/admin/impersonate-stop`) - Ukončí impersonaci a vrátí se k původnímu adminovi.
    - `admin.impersonate.search` - AJAX vyhledávání uživatelů pro UI select.
- **Session:** Původní ID admina se ukládá do session pod klíčem `impersonated_by`.

### UI prvky
- **Topbar (Admin):** Ikona "tajného agenta" (`fa-user-secret`) v horní liště administrace otevírá vyhledávací pole pro rychlou impersonaci.
- **Tabulka uživatelů:** Akce "Impersonovat" je dostupná v menu každého uživatele (pokud má přihlášený uživatel oprávnění).
- **Banner:** Pokud je impersonizace aktivní, zobrazuje se v horní části obrazovky (v administraci i na frontendu/členské sekci) výrazný žlutý pruh s informací o aktuálním uživateli a tlačítkem pro ukončení.

## 2. Role a Oprávnění
Byla zavedena nová struktura rolí s využitím `spatie/laravel-permission`.

### Definované role
1. **Admin:** Má plný přístup ke všem funkcím včetně admin nástrojů (impersonace, správa uživatelů, pokročilé nastavení).
2. **Coach (Trenér):** Má přístup k administraci týmů, soupisek, docházky, zápasů, akcí, statistik a ekonomiky. Nemá přístup k systémovým nástrojům.
3. **Editor:** Má stejná práva jako Coach, ale **nemůže měnit soupisky** (přidávat/odebírat hráče a trenéry v týmech).
4. **Player (Hráč):** Má přístup pouze do členské sekce na frontendu.

### Nová oprávnění (Permissions)
Byla přidána jemnější granulace oprávnění, například:
- `impersonate_users` - Právo přepínat se na jiné uživatele.
- `manage_rosters` - Právo měnit složení týmů (hráči, trenéři).
- `manage_economy` - Právo spravovat platby a finance.
- `manage_matches`, `manage_events`, `manage_attendance` atd.

## 3. Lokalizace oprávnění
Pro uživatelsky přívětivé zobrazení v administraci byly vytvořeny překladové soubory:
- `lang/cs/permissions.php`
- `lang/en/permissions.php`

## 4. Bezpečnostní pravidla
- Impersonace je povolena pouze uživatelům s oprávněním `impersonate_users`.
- Nelze impersonovat sám sebe.
- Při ukončení impersonizace je uživatel bezpečně vrácen k původnímu admin účtu.
- Editorovi jsou v administraci automaticky skryty akce pro úpravu soupisek díky kontrolám oprávnění `manage_rosters`.
