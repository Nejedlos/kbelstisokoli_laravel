# Správa 2FA (Timeout a Zapamatování zařízení)

Tento modul rozšiřuje standardní Laravel Fortify 2FA o bezpečnostní prvky vyžadované pro administraci Kbelští sokoli.

## Účel
Zajištění, aby administrátoři museli periodicky potvrzovat svou identitu pomocí 2FA, i když jsou přihlášeni, a zároveň umožnění "zapamatování" důvěryhodného zařízení.

## Technický popis

### 1. 2FA Timeout
V administraci je zaveden povinný timeout pro 2FA potvrzení. Výchozí hodnota je **2 hodiny** (120 minut).
- Pokud je uživatel neaktivní déle než tento limit, nebo pokud uplyne tento limit od posledního 2FA ověření, bude při pokusu o přístup do administrace znovu vyzván k zadání 2FA kódu.
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
- **Response:** `TwoFactorLoginResponse` a `LogoutResponse` (přetěžují výchozí Fortify/Filament chování).
- **View:** Upravený `auth.two-factor-challenge` s checkboxem.
- **Chybové stránky:** Vlastní `419.blade.php` pro elegantní zvládnutí vypršené relace.

## Konfigurace
V souboru `.env` lze (volitelně, po přidání do configu) nastavit:
- `2FA_TIMEOUT` (v sekundách, výchozí 7200).

## Správa pro administrátory
Pokud uživatel ztratí zařízení, stačí se odhlásit na všech zařízeních, nebo počkat na vypršení 30denní lhůty. Cookie je vázána na ID uživatele.
