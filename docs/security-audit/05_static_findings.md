# Statické nálezy a zranitelnosti (Static Code Analysis)

Tento dokument obsahuje detailní seznam zranitelností nalezených během statické analýzy zdrojového kódu projektu.

## Souhrnná tabulka priorit

| ID | Název | Závažnost | Stav |
|----|-------|-----------|------|
| 1 | Expozice .env.production.bak v public/ | **CRITICAL** | **VYŘEŠENO** |
| 2 | Persistent XSS v CMS blocích (Custom HTML / Rich Text) | **HIGH** | **VYŘEŠENO** |
| 3 | Broken Access Control v MediaDownloadController | **HIGH** | **VYŘEŠENO** |
| 4 | XSS v Feedback Snapshotu | **HIGH** | **VYŘEŠENO** |
| 5 | IDOR v ProfileController (Avatar selection) | **MEDIUM** | **VYŘEŠENO** |
| 6 | XSS ve vyhledávání (HelpSearchService) | **MEDIUM** | **VYŘEŠENO** |
| 7 | CSRF Bypass na Feedback endpointu | **MEDIUM** | **VYŘEŠENO** |
| 8 | Chybějící Policies u citlivých modelů | **MEDIUM** | **VYŘEŠENO** |
| 9 | SSRF v Screenshot Proxy | **MEDIUM** | **VYŘEŠENO** |
| 10 | Neautorizovaná Impersonifikace v Screenshot Proxy | **HIGH** | **VYŘEŠENO** |
| 11 | [ADMIN] Bypass autorizace na custom stránkách | **HIGH** | **VYŘEŠENO** |
| 12 | [ADMIN] Privilege Escalation v UserForm (Roles) | **CRITICAL** | **VYŘEŠENO** |
| 13 | [ADMIN] Systemická absence Laravel Policies | **HIGH** | **VYŘEŠENO** |
| 14 | [ADMIN] Neautorizované akce v UserForm | **MEDIUM** | **VYŘEŠENO** |
| 15 | [ADMIN] Neautorizované Bulk/Header akce v UsersTable | **HIGH** | **VYŘEŠENO** |
| 16 | [ADMIN] Absence Policies pro finanční konfigurace | **HIGH** | **VYŘEŠENO** |
| 17 | [ADMIN] Neomezený přístup k Media Assetům | **MEDIUM** | **VYŘEŠENO** |

---

## 1. Expozice .env.production.bak v public/ [CRITICAL] - VYŘEŠENO
- **Soubor:** `public/.env.production.bak`
- **Nález:** Ve složce `public` se nacházela záložní konfigurace.
- **Status:** Soubor byl smazán a riziko bylo eliminováno.

## 2. Persistent XSS v CMS blocích [HIGH] - VYŘEŠENO
- **Soubory:** 
    - `resources/views/components/public/blocks/custom_html.blade.php`
    - `resources/views/components/public/blocks/rich_text.blade.php`
- **Nález:** Obsah byl renderován bez sanitizace.
- **Status:** Implementována sanitizace pomocí `App\Support\HtmlSanitizer::clean()`.

## 3. Broken Access Control v MediaDownloadController [HIGH] - VYŘEŠENO
- **Soubor:** `app/Http/Controllers/MediaDownloadController.php`
- **Nález:** Chybějící kontrola oprávnění u modelů jiných než `MediaAsset`.
- **Status:** Přidána generická autorizace `$this->authorize('view', $media->model)`.

## 4. XSS v Feedback Snapshotu [HIGH] - VYŘEŠENO
- **Soubor:** `resources/views/feedback/snapshot.blade.php`
- **Nález:** Neúčinná ochrana pomocí `preg_replace`.
- **Status:** Nahrazeno robustní sanitizací přes `HtmlSanitizer`.

## 5. IDOR v ProfileController (Avatar Selection) [MEDIUM] - VYŘEŠENO
- **Soubor:** `app/Http/Controllers/Member/ProfileController.php`
- **Nález:** Chybějící kontrola vlastnictví/veřejnosti u výběru avataru.
- **Status:** Přidána kontrola `is_public` nebo `uploaded_by_id` přímo do validačního pravidla.

## 6. XSS ve vyhledávání (HelpSearchService) [MEDIUM] - VYŘEŠENO
- **Soubor:** `app/Services/Help/HelpSearchService.php`
- **Nález:** Možnost injekce HTML tagů před zvýrazněním.
- **Status:** Implementováno bezpečné escapování `e()` před vložením `<mark>` tagů.

## 7. CSRF Bypass na Feedbacku [MEDIUM] - VYŘEŠENO
- **Soubor:** `bootstrap/app.php`
- **Nález:** Byla nalezena výjimka v CSRF ochraně, která již byla odstraněna. Testy potvrzují funkční ochranu.

## 8. Chybějící Policies u citlivých modelů [MEDIUM] - VYŘEŠENO
- **Soubor:** `app/Policies/`
- **Nález:** Chybějící politiky pro Filament Resources.
- **Status:** Vytvořeny Policies pro všechny klíčové modely (Page, FeedbackReport, atd.).

## 9. SSRF v Screenshot Proxy [MEDIUM] - VYŘEŠENO
- **Soubor:** `app/Http/Controllers/ScreenshotRenderController.php`
- **Nález:** Slabá validace interních URL.
- **Status:** Zpřísněna validace `isInternalUrl` (zakázány `//` a vynuceno `config('app.url')`).

## 10. Neautorizovaná Impersonifikace v Screenshot Proxy [HIGH] - VYŘEŠENO
- **Soubor:** `app/Http/Middleware/DetectScreenshotMode.php`
- **Nález:** Používání `Auth::loginUsingId` v signované URL.
- **Status:** Nahrazeno `Auth::onceUsingId()`, které nepřihlašuje uživatele trvale do session.

## 11. [ADMIN] Bypass autorizace na custom stránkách [HIGH] - VYŘEŠENO
- **Soubory:** 
    - `app/Filament/Pages/DebugOperations.php`
    - `app/Filament/Pages/Documentation.php`
    - `app/Filament/Pages/Help.php`
    - `app/Filament/Pages/SeasonRenewal.php`
- **Nález:** Stránky postrádaly metodu `canAccess()`.
- **Status:** Metoda `canAccess()` byla implementována na všech custom stránkách s kontrolou příslušných oprávnění.

## 12. [ADMIN] Privilege Escalation v UserForm (Roles) [CRITICAL] - VYŘEŠENO
- **Soubor:** `app/Filament/Resources/Users/Schemas/UserForm.php`
- **Nález:** Možnost změny rolí bez autorizace.
- **Status:** Přidána kontrola `visible()` a `disabled()` navázaná na roli `admin`.

## 13. [ADMIN] Systemická absence Laravel Policies [HIGH] - VYŘEŠENO
- **Nález:** Mnoho modelů nemělo definovanou Policy, což ve Filamentu v5 může znamenat otevřený přístup.
- **Status:** Vytvořeny Policies pro všechny modely a zajištěna striktní autorizace v Resources.

## 14. [ADMIN] Neautorizované akce v UserForm [MEDIUM] - VYŘEŠENO
- **Soubor:** `app/Filament/Resources/Users/Schemas/UserForm.php`
- **Nález:** Akce pro aktivaci/deaktivaci účtu nebyla chráněna.
- **Status:** Přidána autorizační kontrola `can('manage_users')`.

## 15. [ADMIN] Neautorizované Bulk/Header akce v UsersTable [HIGH] - VYŘEŠENO
- **Soubor:** `app/Filament/Resources/Users/Tables/UsersTable.php`
- **Nález:** Akce pro slučování uživatelů nebyly chráněny.
- **Status:** Přidána restrikce pouze pro roli `admin`.

## 16. [ADMIN] Absence Policies pro finanční konfigurace [HIGH] - VYŘEŠENO
- **Soubor:** `app/Models/UserSeasonConfig.php`
- **Status:** Implementována `UserSeasonConfigPolicy`.

## 17. [ADMIN] Neomezený přístup k Media Assetům [MEDIUM] - VYŘEŠENO
- **Soubor:** `app/Models/MediaAsset.php`
- **Status:** Implementována `MediaAssetPolicy`.
