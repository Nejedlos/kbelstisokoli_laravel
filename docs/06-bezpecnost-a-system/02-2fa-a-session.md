# Správa 2FA (Timeout a Zapamatování zařízení)

Tento modul rozšiřuje standardní Laravel Fortify 2FA o bezpečnostní prvky vyžadované pro administraci Kbelští sokoli.

## Účel
Zajištění, aby administrátoři museli periodicky potvrzovat svou identitu pomocí 2FA, i když jsou přihlášeni, a zároveň umožnění "zapamatování" důvěryhodného zařízení.

## Technický popis

### 1. 2FA Timeout
V administraci je zaveden povinný timeout pro 2FA potvrzení. Výchozí hodnota je **24 hodin**.
- Pokud uplyne tento limit od posledního 2FA ověření, bude při pokusu o přístup do administrace znovu vyzván k zadání 2FA kódu.
- Samotná relace (1. faktor) má nastavený timeout na **3 hodiny** (180 minut).
- Toto chování zajišťuje middleware `CheckTwoFactorTimeout`.

### 2. Zapamatování zařízení (Remember Device)
Na stránce 2FA ověření je k dispozici volba **"Zapamatovat si toto zařízení na 30 dní"**.
- Pokud je zaškrtnuto, systém nastaví podepsanou a šifrovanou cookie `2fa_remember`.
- Dokud je tato cookie platná a odpovídá přihlášenému uživateli, middleware `CheckTwoFactorTimeout` automaticky prodlužuje platnost 2FA relace a uživatel není obtěžován výzvou ke kódu.

### 3. Úplné odhlášení
Při kliknutí na "Odhlásit se" (Logout) dojde k:
- Zneplatnění session.
- Vygenerování nového CSRF tokenu.
- **Odstranění 2FA zapamatování** (vymazání cookie `2fa_remember`).
- Tím je zajištěno, že po logoutu je pro příští přihlášení na stejném zařízení 2FA opět vyžadováno (pokud nebylo zvoleno zapamatování při novém loginu).

Nově je také k dispozici globální odhlašovací URL **/admin/logout**, která podporuje i metodu **GET** (pro snadný přístup přímým odkazem) a vyvolává stejnou logiku bezpečného odhlášení.

## Implementované komponenty
- **Middleware:** `CheckTwoFactorTimeout` (registrovaný jako `2fa.timeout`).
    - *Novinka:* Automaticky ukládá zamýšlenou URL (`intended`), aby se uživatel po potvrzení 2FA vrátil přesně tam, kam směřoval.
- **Response:** `TwoFactorLoginResponse` a `LoginResponse` (přetěžují výchozí Fortify/Filament chování).
    - *Zlepšení:* Robustní detekce adminů (oprávnění + role) a automatické přesměrování do `/admin` po přihlášení. Pokud admin směřuje na obecný dashboard členské sekce, je tento záměr přebit administrací.
- **View:** Upravený `auth.two-factor-challenge` s checkboxem.
- **Chybové stránky:** Vlastní `419.blade.php` pro elegantní zvládnutí vypršené relace.

## Konfigurace a synchronizace (Seeding)
- `AUTH_2FA_TIMEOUT` (v sekundách, výchozí 86400).
- `SESSION_LIFETIME` (v minutách, výchozí 180).

### Synchronizace 2FA mezi prostředími
Pro usnadnění přechodu z lokálního vývoje na produkci byl vytvořen seeder `UserSecuritySeeder`.
- **Účel:** Přenáší nastavení 2FA (secret, recovery codes) a hesla pro klíčové uživatele (např. `nejedlymi@gmail.com`).
- **Použití:** `php artisan db:seed --class=UserSecuritySeeder` (nebo automaticky přes `GlobalSeeder`).
- **Pozor:** Seeder obsahuje zašifrovaná citlivá data a měl by být používán s vědomím toho, že přepisuje bezpečnostní nastavení v cílové databázi.

## Správa pro administrátory
Pokud uživatel ztratí zařízení, stačí se odhlásit na všech zařízeních, nebo počkat na vypršení 30denní lhůty. Cookie je vázána na ID uživatele.
