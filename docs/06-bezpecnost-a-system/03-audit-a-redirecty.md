# Audit Autentizace, Autorizace a Redirectů

Tento dokument shrnuje audit a úpravy systému přístupů, rolí a přesměrování v projektu Kbelští sokoli.

## 1. Architektura přístupů
Projekt používá několik úrovní zabezpečení:
- **Auth Guards:** Primárně se používá guard `web` pro frontend i administraci.
- **Role a Oprávnění:** Využívá se balíček `spatie/laravel-permission`. Hlavní role jsou `admin`, `editor`, `coach` a `player`.
- **Filament Admin Panel:** Přístup je řízen metodou `canAccessPanel` v modelu `User`, která deleguje na `canAccessAdmin()`.
- **2FA (Two-Factor Authentication):** Pro administrátory je 2FA povinné pro přístup k `admin*` routám. Zajišťuje to middleware `EnsureTwoFactorEnabled`.

## 2. Nalezené a opravené problémy
- **Nekonzistentní redirecty:** `LoginResponse` a `TwoFactorLoginResponse` měly duplicidní a mírně odlišnou logiku pro rozhodování, kam uživatele poslat po přihlášení.
- **Ignorování Intended URL u adminů:** Administrátoři byli vždy posíláni na `/admin`, i když přišli z konkrétního odkazu (např. z e-mailu na konkrétní detail v adminu).
- **Zacyklení Intended URL:** Hrozilo zacyklení, pokud `intended` URL v session ukazovala na login nebo logout stránku.
- **Role v testech:** Testy selhávaly na chybějících rolích v databázi (opraveno pomocí `Role::findOrCreate`).
- **Pevné URL v UI:** V některých Blade komponentách byly natvrdo napsané cesty jako `/admin/login` místo použití pojmenovaných rout.

## 3. Provedené změny

### 3.1 Sjednocení redirect logiky (`App\Support\AuthRedirect`)
Byla vytvořena centrální třída `AuthRedirect`, která obsahuje robustní logiku pro určení cílové URL:
- **Priorita 1:** Pokud je nastaveno `intended` URL a je to bezpečná adresa (ne login/logout/2FA), použije se.
- **Priorita 2:** Pokud je admin a míří na obecný dashboard členské sekce, preferuje se `/admin`.
- **Priorita 3:** Pokud není `intended`, použije se výchozí fallback dle role (`/admin` pro adminy, `/clenska-sekce/dashboard` pro členy).

### 3.2 Refaktor responzí
- `App\Http\Responses\LoginResponse` nyní využívá `AuthRedirect`.
- `App\Http\Responses\TwoFactorLoginResponse` nyní využívá `AuthRedirect`.

### 3.3 Úpravy UI
- `resources/views/components/header.blade.php`: Opraveny odkazy na login a administraci. Administrace se nyní zobrazuje dynamicky dle `canAccessAdmin()` a používá konfigurovanou cestu.
- `resources/views/components/footer.blade.php`: Opraven odkaz na Členskou sekci pomocí `route('login')`.

### 3.4 Opravy v testech
- `app/Filament/Pages/SystemConsole.php`: Ikona změněna na `heroicon-o-command-line`, aby se předešlo `SvgNotFound` výjimce v testovacím prostředí bez FontAwesome Pro.
- Doplněny testy `tests/Feature/AuthRedirectTest.php` pro ověření všech scénářů přesměrování.

## 4. Ověřené scénáře (Test Matrix)
| Uživatel | Cíl | Výsledek |
| :--- | :--- | :--- |
| Guest | `/` | Přístup povolen |
| Guest | `/clenska-sekce/dashboard` | Redirect na `/login` |
| Guest | `/admin` | Redirect na `/login` |
| Member | `/login` | Redirect na `/clenska-sekce/dashboard` |
| Admin | `/login` | Redirect na `/admin` |
| Admin + Member | `/login` | Redirect na `/admin` (preferováno) |
| Admin | `/admin/settings` (intended) | Redirect na `/admin/settings` po loginu/2FA |
| Deaktivovaný uživatel | Jakákoliv chráněná route | Logout a redirect na login s chybou |

## 5. Doporučení pro další vývoj
- **Automatizované testy:** Pravidelně spouštět `php artisan test tests/Feature/AuthAccessTest.php` a `tests/Feature/AuthRedirectTest.php`.
- **Sledování 2FA:** Monitorovat logy `2FA.ensure` v případě hlášených problémů s přístupem do administrace.
