# Screenshot pipeline (Playwright → html-to-image → html2canvas)

Tento dokument popisuje architekturu a provoz vícevrstvého řešení pro pořizování screenshotů ve feedback widgetu.

## Architektura
1. Primární vrstva: server-side screenshot přes Playwright (Chromium, headless). Podporuje moderní CSS barvy (oklab, oklch).
2. Fallback #1: klientský screenshot přes html-to-image (využívá SVG foreignObject, velmi věrný render).
3. Fallback #2: klientský screenshot přes html2canvas nad sanitizovaným klonem DOMu (poslední záchrana).
4. Poslední fallback: bez screenshotu (odeslání reportu s DOM snapshotem a logy), aby akce nikdy neselhávala tvrdou chybou.

## Konfigurace
- `config/feedback.php`
  - `screenshot.strategy`: `auto|playwright|html-to-image|html2canvas|none` (výchozí `auto`).
  - `screenshot.playwright.enabled`: zapnutí/vypnutí server-side.
  - `screenshot.playwright.timeout`: timeout v ms (default 30000).
  - `screenshot.playwright.node_path`: binárka Node (default `node`).
  - `screenshot.playwright.script_path`: `resources/js/screenshot-worker.cjs`.

## Endpointy
- `POST /feedback/screenshot` – přijme DOM snapshot a vrátí screenshot (base64) pořízený Playwrightem.
- `GET /feedback/snapshot/{token}` – bezpečný jednorázový render DOMu pro Playwright (token je v cache, krátká expirace).

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

## Playwright worker
- Skript: `resources/js/screenshot-worker.cjs`
- Spouštění: Symfony Process z PHP služby `App\Services\ScreenshotService`.
- Parametry: `--url`, `--selector`, `--out`, `--width`, `--height`, `--dpr`, `--fullPage`.

## Instalace závislostí
- Node 18+ (doporučeno LTS, v dev ověřeno s Node v25).
- NPM balíčky: `npm install`
- Playwright Chromium: `npm run playwright:install`
- Build assetů: `npm run build`

## Provoz na serveru
- Zajistit dostupnost Node a Playwright (Chromium). Na některých hostinzích může být nutná separátní služba/runner.
- Nastavit ENV:
  - `FEEDBACK_SCREENSHOT_STRATEGY=auto` (prod)
  - `FEEDBACK_PLAYWRIGHT_ENABLED=true|false` dle dostupnosti Node/Chromia

## Testování
- Jednotkové testy: `tests/Unit/CssSanitizerTest.php`
- Integrační test náhledu: `tests/Feature/FeedbackSnapshotRouteTest.php`

## Poznámky
- `dom-to-image-more` byl odstraněn – nespolehlivé na CORS a moderní CSS.
- html2canvas fallback prochází a čistí inline styly i vložené `<style>` bloky.
- `html-to-image` slouží jako sekundární klientský fallback využívající SVG foreignObject.
- Implementována ochrana proti 419 (CSRF) u feedback endpointů patterny `feedback*` a `*/feedback*`.
