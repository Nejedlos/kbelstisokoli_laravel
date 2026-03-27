# Plán runtime testů (Security Test Plan)

Tento dokument definuje konkrétní testovací scénáře pro ověření bezpečnosti aplikace v reálném čase. Testy jsou navrženy tak, aby byly bezpečné, neinvazivní a reverzibilní.

## Metodika
- **Prostředí:** Testování probíhá proti lokálnímu vývojovému prostředí nebo stagingu.
- **Payloady:** Používáme výhradně bezpečné payloady (např. `alert(1)`, `console.log('XSS')`, `<mark>`).
- **Nástroje:** PHPUnit (Laravel Feature Tests), `curl`, `browser dev tools`.

---

## 1. Autentizace a Session Management

### ID: TEST-AUTH-01 (Session & Cookie Flags)
- **Cíl:** Ověřit, že session cookies mají nastaveny bezpečné příznaky (`HttpOnly`, `Secure`, `SameSite=Lax/Strict`).
- **Předpoklady:** Aplikace běží na HTTPS (pro `Secure` flag).
- **Postup:** 
    1. Otevřít prohlížeč, přihlásit se do administrace.
    2. V DevTools (Application -> Cookies) zkontrolovat vlastnosti `XSRF-TOKEN` a `laravel_session`.
- **Očekávaný výsledek:** `HttpOnly` je true (pro session), `Secure` je true, `SameSite` je Lax nebo Strict.
- **Fail:** Chybějící `HttpOnly` nebo `Secure` flag u citlivých cookies.
- **Logování:** Snímek obrazovky z DevTools nebo výstup `curl -I`.

### ID: TEST-AUTH-02 (2FA Enforcement)
- **Cíl:** Ověřit, že admin routy vyžadují 2FA.
- **Předpoklady:** Účet s rolí `admin` má vypnuté 2FA (pro test bypassu).
- **Postup:**
    1. Pokusit se o přístup na `/admin` s platným heslem, ale bez dokončeného 2FA flow.
- **Očekávaný výsledek:** Přesměrování na 2FA challenge stránku.
- **Fail:** Přímý přístup do Dashboardu bez 2FA.
- **Logování:** HTTP Response kód a URL po login.

---

## 2. Autorizace a IDOR

### ID: TEST-AUTHZ-01 (Broken Access Control - Media Download)
- **Cíl:** Ověřit, zda lze stáhnout soukromá média jiných uživatelů bez autorizace.
- **Předpoklady:** Znát UUID souboru, který nepatří testovacímu uživateli (např. finanční doklad).
- **Postup:**
    1. Jako nepřihlášený uživatel (nebo uživatel bez oprávnění) zavolat `GET /media/download/{uuid}`.
- **Očekávaný výsledek:** HTTP 403 Forbidden nebo 404 Not Found.
- **Fail:** HTTP 200 OK a stažení souboru.
- **Logování:** HTTP status kód.

### ID: TEST-AUTHZ-02 (IDOR - Avatar Selection)
- **Cíl:** Ověřit, zda si uživatel může nastavit jako avatar cizí nebo soukromé médium.
- **Předpoklady:** Útočník zná ID `MediaAsset`, který mu nepatří.
- **Postup:**
    1. Odeslat `POST /member/profile/avatar/select` s `asset_id` cizího souboru.
- **Očekávaný výsledek:** Validace selže nebo HTTP 403.
- **Fail:** Úspěšná změna avataru na cizí soubor.
- **Logování:** Obsah JSON odpovědi.

---

## 3. Input Validation a XSS

### ID: TEST-XSS-01 (Persistent XSS - CMS Blocks)
- **Cíl:** Ověřit sanitizaci CMS bloků (Custom HTML / Rich Text).
- **Předpoklady:** Přístup do administrace (role s právem editovat stránky).
- **Postup:**
    1. Vytvořit CMS blok typu Custom HTML s obsahem: `<script>console.log('XSS-CMS-TEST')</script>`.
    2. Zobrazit danou stránku na frontendu.
- **Očekávaný výsledek:** Skript se nespustí (je escapován nebo odstraněn sanitizérem).
- **Fail:** V konzoli prohlížeče se objeví zpráva 'XSS-CMS-TEST'.
- **Logování:** HTML kód vygenerované stránky.

### ID: TEST-XSS-02 (XSS - Feedback Snapshot)
- **Cíl:** Ověřit, zda je DOM snapshot ve feedbacku sanitizován.
- **Předpoklady:** Funkční feedback widget.
- **Postup:**
    1. Odeslat feedback se zmanipulovaným DOMem obsahujícím `<img src=x onerror="console.log('XSS-FEEDBACK')">`.
    2. Jako administrátor zobrazit snapshot v administraci.
- **Očekávaný výsledek:** Skript se nespustí (v konzoli nic není).
- **Fail:** Spuštění skriptu v kontextu administrace.
- **Logování:** Snímek konzole v administraci.

---

## 4. CSRF Ochrana

### ID: TEST-CSRF-01 (Bypass na Feedback endpointu)
- **Cíl:** Ověřit, zda endpoint `/feedback` (POST) vyžaduje CSRF token.
- **Předpoklady:** `bootstrap/app.php` obsahuje výjimku pro tuto routu (nález ze statické analýzy).
- **Postup:**
    1. Použít `curl` nebo Postman k odeslání POST požadavku na `/feedback` bez `X-CSRF-TOKEN` hlavičky a bez cookie.
- **Očekávaný výsledek:** HTTP 419 Page Expired (pokud je ochrana aktivní).
- **Fail:** HTTP 200/201 (feedback byl přijat bez tokenu).
- **Logování:** HTTP response kód.

---

## 5. File Upload a Import

### ID: TEST-UPLOAD-01 (Malicious File Upload - Avatar)
- **Cíl:** Ověřit, zda lze nahrát neobrázkový soubor (např. `.php`, `.html`) jako avatar.
- **Předpoklady:** Přístup do profilu člena.
- **Postup:**
    1. Pokusit se nahrát soubor `test.php` (s obsahem `<?php phpinfo();`) přes formulář pro avatar.
- **Očekávaný výsledek:** Validace na straně serveru odmítne soubor (včetně kontroly MIME typu, nejen přípony).
- **Fail:** Soubor je nahrán do storage.
- **Logování:** Chybová zpráva validačního requestu.

---

## 6. SSRF a Impersonifikace

### ID: TEST-SSRF-01 (Screenshot Proxy SSRF)
- **Cíl:** Ověřit, zda lze screenshotovat interní adresy (localhost).
- **Předpoklady:** Přístup k `ScreenshotRenderController` (vyžaduje signovanou URL).
- **Postup:**
    1. Podvrhnout parametr `url` v signované URL (pokud je to možné bez porušení podpisu) nebo testovat v controlleru validaci pro adresy jako `http://localhost`, `http://127.0.0.1`, `http://169.254.169.254`.
- **Očekávaný výsledek:** Validátor odmítne jakoukoliv URL, která není explicitně povolena (whitelist).
- **Fail:** Screenshoter se pokusí o přístup k interní adrese.
- **Logování:** Výstup z validační logiky.

---

## 7. Informace a Debug Exposure

### ID: TEST-INFO-01 (Sensitive File Exposure)
- **Cíl:** Ověřit nedostupnost citlivých souborů přes web.
- **Předpoklady:** Soubory jako `.env.production.bak` existovaly v `public/`.
- **Postup:**
    1. Zavolat `GET /.env`, `GET /.env.production.bak`, `GET /storage/logs/laravel.log`.
- **Očekávaný výsledek:** HTTP 404 nebo 403.
- **Fail:** HTTP 200 OK a zobrazení obsahu.
- **Logování:** HTTP status kód.

---

## 8. Browser Security a Headers

### ID: TEST-HEADER-01 (Security Headers Check)
- **Cíl:** Ověřit přítomnost bezpečnostních hlaviček (`X-Frame-Options`, `Content-Security-Policy`, `Strict-Transport-Security`).
- **Postup:** 
    1. `curl -I https://new.kbelstisokoli.cz/`
- **Očekávaný výsledek:** Přítomnost hlaviček zmírňujících Clickjacking a XSS.
- **Fail:** Chybějící hlavičky.
- **Logování:** Kompletní seznam HTTP hlaviček.

---

## Seznam oblastí k prioritnímu ověření (Runtime)

| Priorita | Oblast | ID Testů |
|----------|--------|----------|
| **CRITICAL** | Expozice citlivých souborů | TEST-INFO-01 |
| **HIGH** | Persistent XSS (CMS, Feedback) | TEST-XSS-01, TEST-XSS-02 |
| **HIGH** | Broken Access Control & IDOR | TEST-AUTHZ-01, TEST-AUTHZ-02 |
| **HIGH** | Impersonifikace a SSRF | TEST-SSRF-01 |
| **MEDIUM** | CSRF Bypass | TEST-CSRF-01 |
| **MEDIUM** | Security Headers | TEST-HEADER-01 |
