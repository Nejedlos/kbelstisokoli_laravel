# Mapování projektu a útokové plochy

Tento dokument mapuje hlavní vstupní body, privilegované části systému a citlivé operace projektu Kbelští sokoli pro účely bezpečnostního auditu.

## 1. Architektura a technologie
- **Framework:** Laravel 12.0
- **Administrace:** Filament PHP v5 (s využitím Livewire 3)
- **Autentizace:** Laravel Fortify (s povinnou 2FA pro adminy)
- **Autorizace:** Spatie Laravel Permission
- **Frontend:** Laravel Folio, Livewire, Alpine.js, Tailwind v4
- **Ostatní:** Sanctum, Scout, Spatie Media Library, Playwright (pro screenshoty)

## 2. Hlavní vstupní body (Attack Surface)

### 2.1 Veřejně přístupné části
- **URL:** `/`, `/o-klubu`, `/tym/...`, `/zapas/...` atd.
- **Rizika:** 
    - SQL Injection v parametrech URL (např. slugy, ID).
    - Open Redirect v `LanguageController` při přepínání jazyků (parametr `previous_url` je rekonstruován z parse_url).
    - SSRF přes `ScreenshotRenderController` (zabezpečeno `isInternalUrl`, ale citlivé na bypass typu `//127.0.0.1`).

### 2.2 Členská sekce (Member Section)
- **Prefix:** `/clenska-sekce`
- **Omezení:** Middleware `auth`, `verified`, `active`, `permission:view_member_section`.
- **Klíčové operace:**
    - **Profil (`ProfileController`):** Změna hesla, osobních údajů.
    - **Avatar (`AvatarModal` - Livewire):** Nahrávání, dekódování Base64, ořezávání, manipulace s `public/uploads/defaults` (pro adminy).
    - **Docházka (`AttendanceController`):** Bulk akce (`bulkStore`).
    - **Nápověda (`HelpCenter` - Livewire):** Interakce s AI, prohledávání dokumentace.

### 2.3 Administrace (Filament)
- **URL:** `/admin`
- **Omezení:** Middleware `auth`, `active`, `2fa.required`, `2fa.timeout`, `permission:access_admin`.
- **Klíčové operace:**
    - **Správa uživatelů (`UsersTable`):** Bulk aktivace/deaktivace, hromadné mazání, automatické slučování "ghost" uživatelů (`UserMergeService`).
    - **Ekonomika a Finance:** Správa plateb, přiřazování pohledávek.
    - **Zápasy a Sportovní výsledky:** Správa dat o zápasech a hráčích.
    - **Custom Stránky:** 
        - `DebugOperations`: Potenciálně nebezpečné systémové operace.
        - `SystemConsole`: Přímá interakce se systémovými příkazy/konfigurací.
        - `Documentation`: Přístup k interním návodům.
    - **Impersonifikace:** `ImpersonateController` (AJAX search, login as user).

### 2.4 Zpětná vazba a Feedback (Kritické)
- **URL:** `/feedback`, `/feedback/screenshot`, `/feedback/snapshot/{token}`
- **Vlastnosti:** 
    - Částečné výjimky z CSRF (dříve ošetřeno v `bootstrap/app.php`, vyžaduje opětovnou kontrolu po změnách v Laravel 12).
    - **Server-side Screenshoty:** Využití Playwright (`ScreenshotService`) pro generování screenshotů z uživatelského DOMu. 
    - **Impersonifikace pro render:** `ScreenshotRenderController` využívá `Auth::loginUsingId($userId)` pro získání kontextu uživatele při renderování stránky pro screenshot (vysoké riziko při úniku signované URL).
    - **Rizika:** XSS (vstříknutí skriptu do snapshotu), SSRF (donucení serveru navštívit interní URL), RCE (zranitelnost v Node.js/Playwright workeru).

### 2.5 Systémové a API endpointy
- **Cron/Schedule:** `/system/schedule/{token}`, `/system/cron/run` (chráněno tokeny v konfiguraci).
- **Média:** `/media/download/{uuid}` (chráněno logikou `MediaDownloadController` a `access_level`). 
    - *Poznámka:* Kontrola oprávnění probíhá pouze pro model `MediaAsset`, ostatní modely jsou v aktuální verzi `MediaDownloadController` nekontrolované (potenciální IDOR).
- **API:** `/api/user` (Sanctum auth).

## 3. Privilegované části a Trust Boundaries
- **Trust Boundary 1 (Veřejnost -> Uživatel):** Přihlášení (Fortify), registrace (není-li vypnuta).
- **Trust Boundary 2 (Uživatel -> Admin):** Role/Permission check, vynucená 2FA.
- **Trust Boundary 3 (Admin -> System):** Přímé souborové operace, spouštění Artisan příkazů (přes Filament stránky).

## 4. Datové toky a citlivé operace
1. **User Uploads:** Livewire (`AvatarModal`) -> Dočasné uložení -> Ořez -> Media Library -> S3/Local Storage.
2. **Feedback Flow:** Uživatel -> DOM Snapshot -> Server (`FeedbackController`) -> Playwright Worker -> PNG Screenshot -> Uložení -> Admin náhled.
3. **Impersonation Flow:** Admin -> `ImpersonateController` -> `session()->invalidate()` -> `Auth::login($userToImpersonate)` -> `session()->put('impersonated_by', $adminId)`. 
4. **External Sync:** Admin -> `PlayerSyncService` -> Externí API -> Aktualizace DB (Basketball stats).

## 5. Seznam oblastí s nejvyšší prioritou auditu

| Priorita | Oblast | Hlavní rizika |
| :--- | :--- | :--- |
| **CRITICAL** | **Feedback & Screenshot Pipeline** | XSS (Persistent), SSRF, RCE v Playwright workeru. |
| **HIGH** | **Impersonifikace & Session Management** | Privilege Escalation, obcházení 2FA, session fixation. |
| **HIGH** | **Avatar & File Uploads** | Path Traversal, Arbitrary File Upload, Insecure Direct Object Reference (IDOR). |
| **MEDIUM** | **Filament Bulk Actions & Services** | Mass Assignment, neautorizované hromadné operace (např. v `UserMergeService`). |
| **MEDIUM** | **System & Cron Endpoints** | Leak tokenů v logách, neautorizované spouštění úloh (DoS nebo zneužití logiky). |
| **LOW** | **Public Redirects & Locales** | Open Redirect, session manipulation přes `LanguageController`. |

---
*Zpracoval: Junie (Senior Security Engineer)*
*Datum: 2026-03-27*
