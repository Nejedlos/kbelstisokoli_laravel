# Plán bezpečnostních testů

## 1. Testování autentizace a autorizace
### 1.1 Obcházení 2FA
- **Scénář:** Zkusit se dostat do adminu s platnými údaji, ale bez 2FA kódu.
- **Očekávaný výsledek:** Redirekce na 2FA challenge.
- **Metoda:** Runtime (pokud je k dispozici testovací uživatel) nebo Static review middleware.

### 1.2 Přístup k admin trasám nepovolanými
- **Scénář:** Zkusit přistoupit k `/admin` jako uživatel bez `access_admin` role.
- **Očekávaný výsledek:** 403 Forbidden nebo redirect.

### 1.3 Impersonifikace
- **Scénář:** Zkusit spustit `/admin/impersonate/{userId}` bez oprávnění `impersonate_users`.
- **Očekávaný výsledek:** 403 Forbidden nebo redirect.

## 2. Testování Screenshot Proxy (SSRF / Auth bypass)
### 2.1 Neautorizované přihlášení
- **Scénář:** Zkusit přistoupit k `/system/screenshot/render` s vymyšleným `user_id` bez platné signatury.
- **Očekávaný výsledek:** 401 Unauthorized.

### 2.2 SSRF (Server Side Request Forgery)
- **Scénář:** Zkusit předat `url` parametr s externí adresou nebo interní IP (např. metadata služby).
- **Očekávaný výsledek:** 403 Forbidden (kontrola hostitele v `isInternalUrl`).

## 3. Testování CSRF na feedback endpointu
### 3.1 CSRF bypass
- **Scénář:** Vytvořit HTML formulář na jiném webu a odeslat POST na `/feedback`.
- **Očekávaný výsledek:** Data se uloží (kvůli výjimce z CSRF). Zhodnotit dopad (spam, zahlcení).

## 4. Testování SQL Injection
- **Metoda:** Statická analýza Eloquent dotazů.
- **Zaměření:** Custom `whereRaw` nebo `DB::raw` volání, která by mohla používat neescapované vstupy.

## 5. Testování XSS (Cross-Site Scripting)
- **Metoda:** Statická analýza Blade šablon a Filament komponent.
- **Zaměření:** Použití `{!! !!}` místo `{{ }}` u uživatelských vstupů (zejména jména uživatelů, popisy týmů, zpětná vazba).

## 6. Testování Uploadů
- **Soubor:** `app/Http/Controllers/Member/ProfileController.php` (updateAvatar).
- **Scénář:** Zkusit uploadovat soubor, který není obrázek (např. `.php` nebo `.html`).
- **Očekávaný výsledek:** Validace selže.
