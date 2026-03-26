# Screenshot Systém

Tento dokument popisuje globální řešení pro generování screenshot-friendly HTML výstupů v projektu Kbelští sokoli.

## 1. Účel
Systém umožňuje aplikaci přepnout se do speciálního "Screenshot režimu", ve kterém je HTML výstup optimalizován pro headless prohlížeče (Playwright). Cílem je zajistit vizuální stabilitu a absolutní URL všech assetů.

## 2. Architektura
- **`App\Support\ScreenshotMode`**: Centrální třída pro správu stavu a detekci režimu.
- **`App\Http\Middleware\DetectScreenshotMode`**: Globální middleware, který detekuje požadavek na screenshot a aktivuje režim.
- **`App\Http\Controllers\ScreenshotRenderController`**: Endpoint pro bezpečné renderování libovolných interních stránek.
- **Blade Komponenty**: `x-screenshot.styles` a `x-screenshot.scripts` zajišťují stabilizaci UI (vypnutí animací, signalizace připravenosti).

## 3. Jak aktivovat Screenshot režim
Režim lze aktivovat několika způsoby:
1. **Query parametr:** `?screenshot=1` (vhodné pro lokální testování).
2. **HTTP Header:** `X-Screenshot-Mode: 1`.
3. **Podepsaná URL:** Pomocí `URL::signedRoute()` nebo `URL::temporarySignedRoute()`.
4. **Interní token:** Přes header `X-Screenshot-Token` (hodnota definována v `.env`).

## 4. Screenshot Render Endpoint
Aplikace poskytuje globální endpoint:
`GET /system/screenshot/render?url={interni_url}`

Tento endpoint:
1. Ověří, že cílová URL je interní.
2. Interně zavolá cílovou stránku se screenshot signálem.
3. Opraví relativní URL assetů na absolutní.
4. Vrátí čisté HTML připravené pro Playwright.

**Zabezpečení:** Doporučuje se tento endpoint volat s podepsanou URL nebo interním tokenem.

## 5. Stabilizace a Signalizace (JS/CSS)
V screenshot režimu systém automaticky:
- Vypne všechny CSS animace a transition.
- Skryje problematické prvky (cookie lišty, chat widgety, overlaye).
- Přepne `loading="lazy"` u obrázků na `eager`.
- Nastaví `window.__SCREENSHOT_READY__ = true` a atribut `data-screenshot-ready="1"` na `<html>` elementu, jakmile je stránka stabilní (načteny fonty, Livewire inicializován, uplynul delay).

## 6. Konfigurace (.env)
```env
# Zapnutí/vypnutí systému
SCREENSHOT_MODE_ENABLED=true

# Interní token pro komunikaci s NAS (volitelné)
SCREENSHOT_INTERNAL_TOKEN=vas_tajny_token

# Stabilizační delay v ms (výchozí 500)
SCREENSHOT_STABILITY_DELAY=500
```

## 7. Příklad použití (Playwright na NAS)
Playwright služba by měla:
1. Zavolat `/system/screenshot/render?url=/nejaka/stranka`.
2. Vložit získané HTML do prohlížeče (nebo nechat Playwright otevřít tento endpoint přímo, pokud je autorizován).
3. Čekat na signalizaci:
   ```javascript
   await page.waitForFunction(() => window.__SCREENSHOT_READY__ === true);
   ```
4. Pořídit screenshot.
