# Runtime Findings (Security Audit)

Tento dokument obsahuje výsledky runtime ověření bezpečnosti aplikace Kbelští sokoli podle [Plánu runtime testů (03_test_plan.md)](03_test_plan.md).

## Souhrn testů

| ID Testu | Cíl | Výsledek | Závažnost |
|----------|-----|----------|-----------|
| TEST-INFO-01 | Expozice citlivých souborů | **PASS** | INFO |
| TEST-HEADER-01 | Security Headers Check | **FAIL** | **MEDIUM** |
| TEST-AUTH-01 | Session & Cookie Flags | **PASS** | INFO |
| TEST-CSRF-01 | CSRF na Feedbacku | **PASS** | INFO |
| TEST-XSS-01 | Persistent XSS - CMS Blocks | **FAIL** | **HIGH** |
| TEST-XSS-02 | XSS - Feedback Snapshot | **FAIL** | **HIGH** |
| TEST-AUTHZ-01 | Broken Access Control - Media | **FAIL** | **HIGH** |
| TEST-AUTHZ-02 | IDOR - Avatar Selection | **PASS** | INFO |
| TEST-SSRF-01 | Screenshot Proxy SSRF | **PASS** | INFO |

---

## Detaily nálezů (FAIL)

### TEST-INFO-01: Expozice citlivých souborů
- **Status:** **PASS**
- **Ověřeno:** Absence souboru `public/.env.production.bak` v kořenovém adresáři webu.
- **Dopad:** Riziko úniku citlivých dat bylo eliminováno odstraněním souboru z produkčního prostředí.
- **Reprodukce:** `ls -la public/.env.production.bak` vrací 404/Not Found.
- **Poznámka:** Uživatel potvrdil smazání souboru a jeho správu pouze v lokálním (offline) režimu.

### TEST-HEADER-01: Security Headers Check
- **Status:** **FAIL**
- **Ověřeno:** Chybějící hlavička `X-Frame-Options` v odpovědích aplikace.
- **Dopad:** Aplikace je zranitelná vůči Clickjackingu.
- **Reprodukce:** `php artisan test tests/security/BasicSecurityHeadersTest.php`
- **Doporučení:** Přidat `X-Frame-Options: SAMEORIGIN`.

### TEST-XSS-01: Persistent XSS - CMS Blocks
- **Status:** **FAIL**
- **Ověřeno:** CMS bloky typu `Custom HTML` a `Rich Text` umožňují vkládání nebezpečných tagů a skriptů bez sanitizace na výstupu.
- **Dopad:** Persistent XSS na veřejných stránkách webu.
- **Reprodukce:** Vložení `<script>alert(1)</script>` do `custom_html` bloku v administraci.
- **Doporučení:** Implementovat sanitizaci obsahu (např. přes `mews/purifier`) před uložením nebo při renderování.

### TEST-XSS-02: XSS - Feedback Snapshot
- **Status:** **FAIL** (Částečně opraveno, ale neúčinně)
- **Ověřeno:** V `feedback/snapshot.blade.php` byl přidán `preg_replace` pro odstranění `<script>` tagů. Nicméně inline eventy (např. `onerror`, `onclick`) a další XSS vektory zůstávají nefiltrovány.
- **Dopad:** Persistent XSS v administraci při prohlížení feedbacku stále hrozí.
- **Reprodukce:** Odeslání feedbacku s DOM obsahujícím `<img src=x onerror=alert(1)>`. Skript se spustí v administraci.
- **Doporučení:** Použít robustní HTML sanitizér (např. `mews/purifier`) nebo renderovat v striktním iframe sandboxu.

### TEST-AUTHZ-01: Broken Access Control - Media Download
- **Status:** **FAIL**
- **Ověřeno:** `MediaDownloadController` kontroluje oprávnění pouze u `MediaAsset`. Ostatní modely (např. přílohy) jsou stahovatelné kýmkoliv, kdo zná UUID.
- **Dopad:** Únik citlivých soukromých dokumentů.
- **Reprodukce:** Volání `/media/download/{uuid}` pro médium, které nepatří k `MediaAsset`.
- **Doporučení:** Přidat autorizaci pro všechny typy modelů, např. přes Policy.

---

## Detaily nálezů (PASS)

### TEST-AUTH-01: Session & Cookie Flags
- **Status:** **PASS**
- **Ověřeno:** Cookies `XSRF-TOKEN` a `laravel_session` mají správně nastaveny příznaky `Secure` (na HTTPS), `HttpOnly` a `SameSite=Lax`.

### TEST-CSRF-01: CSRF na Feedbacku
- **Status:** **PASS**
- **Ověřeno:** Endpoint `/feedback` vyžaduje platný CSRF token. Požadavek bez tokenu vrací 419.
- **Poznámka:** Dřívější statický nález o výjimce v `bootstrap/app.php` byl pravděpodobně již opraven nebo chybně interpretován (testy potvrzují funkční ochranu).
