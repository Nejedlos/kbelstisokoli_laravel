# Screenshot pipeline (Playwright → html-to-image → html2canvas)

Tento dokument popisuje architekturu a provoz vícevrstvého řešení pro pořizování screenshotů ve feedback widgetu.

## Architektura
1. Primární vrstva: server-side screenshot přes vzdálenou službu (Synology NAS Kettnerka) běžící v Dockeru. Podporuje moderní CSS barvy (oklab, oklch) a nevyžaduje instalaci Chromia na webový server.
2. Fallback #1: klientský screenshot přes html-to-image (využívá SVG foreignObject, velmi věrný render).
3. Fallback #2: klientský screenshot přes html2canvas nad sanitizovaným klonem DOMu (poslední záchrana).
4. Poslední fallback: bez screenshotu (odeslání reportu s DOM snapshotem a logy), aby akce nikdy neselhávala tvrdou chybou.

## Konfigurace
- `config/services.php`
  - `screenshot.url`: URL endpointu (např. `https://screenshot.kbelstisokoli.cz/screenshot`).
  - `screenshot.token`: API token pro autorizaci.
  - `screenshot.timeout`: timeout požadavku (vteřiny, default 40).
- `config/feedback.php`
  - `screenshot.strategy`: `auto|playwright|html-to-image|html2canvas|none` (výchozí `auto`).
  - `screenshot.playwright.enabled`: zapnutí/vypnutí server-side screenshotování.

## Endpointy
- `POST /feedback/screenshot` – přijme DOM snapshot a zavolá externí ScreenshotService.
- `GET /feedback/snapshot/{token}` – bezpečný jednorázový render DOMu pro ScreenshotService (token je v cache, krátká expirace). Tento endpoint musí být přístupný z IP adresy NASu.

## Frontend flow
- `resources/js/feedback-widget.js`
  - Přečte `window.KS_FEEDBACK_CONFIG` (injektováno middlewarem).
  - Strategie `auto`/`playwright`:
    1. Pokus o server-side screenshot (bez sanitizace barev, aby byl render 1:1).
    2. Při selhání fallback na `html-to-image`.
    3. Při selhání fallback na `html2canvas` nad sanitizovaným DOM (zachovává fonty/ikony, ale moderní barvy `oklab/oklch` nahrazuje šedou `rgb(120, 120, 120)` pro stabilitu).
    4. Odeslání bez screenshotu.
  - Záznam konzolových logů je stručný (prefix `[FB]`).
  - Trasy `/feedback` a `/feedback/screenshot` mají v `bootstrap/app.php` výjimku z CSRF ochrany pro maximální robustnost.

## Screenshot Service (Remote)
- Služba běží na NASu Kettnerka v Dockeru (Playwright + Node.js API).
- Implementováno v PHP službě `App\Services\ScreenshotService`.
- Komunikace probíhá přes HTTPS s Bearer tokenem.

## Instalace a nastavení (NAS)
- Služba vyžaduje `API_TOKEN` a nastavení `ALLOWED_HOSTS`.
- Na straně Laravelu stačí nastavit proměnné v `.env`:
  - `SCREENSHOT_SERVICE_URL`
  - `SCREENSHOT_SERVICE_TOKEN`
  - `SCREENSHOT_SERVICE_TIMEOUT`

## Testování
- Jednotkové testy: `tests/Unit/CssSanitizerTest.php`
- Integrační test náhledu: `tests/Feature/FeedbackSnapshotRouteTest.php`

## Poznámky
- `dom-to-image-more` byl odstraněn – nespolehlivé na CORS a moderní CSS.
- html2canvas fallback prochází a čistí inline styly i vložené `<style>` bloky.
- `html-to-image` slouží jako sekundární klientský fallback využívající SVG foreignObject.
- Implementována ochrana proti 419 (CSRF) u feedback endpointů patterny `feedback*` a `*/feedback*`.
