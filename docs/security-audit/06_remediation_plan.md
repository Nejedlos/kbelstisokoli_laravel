# Plán nápravných opatření (Remediation Plan)

Tento dokument definuje konkrétní kroky pro odstranění bezpečnostních zranitelností nalezených během auditu projektu Kbelští sokoli.

## Souhrn priorit oprav

| ID | Název | Závažnost | Stav | Priorita |
|----|-------|-----------|------|----------|
| R1 | Bezpečnostní hlavičky (Clickjacking) | **MEDIUM** | Hotovo | Vysoká |
| R2 | XSS v Feedback Snapshotu (Robustní sanitizace) | **HIGH** | Hotovo | Kritická |
| R3 | Broken Access Control v Media Download | **HIGH** | Hotovo | Vysoká |
| R4 | Persistent XSS v CMS blocích (Purifier) | **HIGH** | Hotovo | Vysoká |
| R5 | Zabezpečení Screenshot Proxy (SSRF & Impersonace) | **HIGH** | Hotovo | Vysoká |
| R6 | XSS ve vyhledávání (HelpSearchService) | **MEDIUM** | Hotovo | Střední |
| R7 | IDOR v Avatar Selection | **MEDIUM** | Hotovo | Střední |
| R8 | Doplnění chybějících Policies | **MEDIUM** | Hotovo | Střední |
| R9 | Rate Limiting na citlivé endpointy | **LOW** | Hotovo | Nízká |

---

## Detaily nápravných opatření

### R1: Bezpečnostní hlavičky (Clickjacking)
- **Problém:** Chybějící `X-Frame-Options` hlavička.
- **Řešení:** Přidat globální middleware nebo konfiguraci v `bootstrap/app.php` pro vynucení `X-Frame-Options: SAMEORIGIN`.
- **Dopad:** Prevence Clickjackingu.
- **Testování:** `php artisan test tests/security/BasicSecurityHeadersTest.php`.

### R2: XSS v Feedback Snapshotu
- **Problém:** Nedostatečná sanitizace přes `preg_replace` (nechrání proti inline eventům).
- **Řešení:** 
    1. Instalace `mews/purifier` (HTMLPurifier).
    2. Použití robustní sanitizace v `FeedbackController` nebo přímo v Blade šabloně.
    3. (Alternativa) Renderování v striktně pískovištěm omezeném iframe (`sandbox` atribut).
- **Dopad:** Bezpečné prohlížení nahlášených chyb v administraci.

### R3: Broken Access Control v Media Download
- **Problém:** Chybějící autorizace pro modely jiné než `MediaAsset`.
- **Řešení:** Implementovat generickou kontrolu `$this->authorize('view', $media->model)` v `MediaDownloadController`.
- **Dopad:** Ochrana soukromých dokumentů (platby, osobní údaje).

### R4: Persistent XSS v CMS blocích
- **Problém:** Přímé renderování HTML z databáze bez sanitizace.
- **Řešení:** Použít `clean()` (z balíčku Purifier) na výstupu v `custom_html` a `rich_text` blocích.
- **Dopad:** Prevence zneužití editace obsahu k útoku na návštěvníky a adminy.

### R5: Zabezpečení Screenshot Proxy
- **Problém:** Riziko SSRF a neautorizovaná impersonifikace uživatele.
- **Řešení:** 
    1. Zpřísnit validaci URL (povolit pouze `config('app.url')`).
    2. Odstranit `Auth::loginUsingId` v proxy controlleru.
    3. Předávat potřebná data (např. preview režim) přes bezpečné parametry nebo podepsané tokeny bez nutnosti plného login.
- **Dopad:** Prevence zneužití serveru jako proxy pro vnitřní útoky a ochrana session.

### R6: XSS ve vyhledávání (HelpSearchService)
- **Problém:** Vkládání `<mark>` do neescapovaného textu.
- **Řešení:** Escapovat text pomocí `e()` před aplikací regulárního výrazu pro zvýraznění.
- **Dopad:** Bezpečné zobrazení výsledků hledání v nápovědě.

### R7: IDOR v Avatar Selection
- **Problém:** Chybějící kontrola vlastnictví/viditelnosti `MediaAsset`.
- **Řešení:** Přidat validaci v `AvatarModal` nebo `ProfileController`, která ověří, zda má uživatel právo k danému assetu přistupovat.

### R8: Doplnění chybějících Policies
- **Problém:** Některé citlivé modely (Page, FeedbackReport) nemají definované Laravel Policies.
- **Řešení:** Vygenerovat a implementovat Policies pro tyto modely a zaregistrovat je v `AuthServiceProvider` (pokud se nepoužívá automatická detekce).

### R9: Rate Limiting
- **Problém:** Možnost spamování endpointů jako `/feedback`.
- **Řešení:** Přidat `RateLimiter` do `bootstrap/app.php` nebo přímo k routám.
