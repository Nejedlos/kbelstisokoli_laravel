# Screenshot pipeline (Playwright → html2canvas → graceful)

Tento dokument popisuje architekturu a provoz vícevrstvého řešení pro pořizování screenshotů ve feedback widgetu.

## Architektura
1. Primární vrstva: server-side screenshot přes Playwright (Chromium, headless).
2. Fallback: klientský screenshot přes html2canvas nad sanitizovaným klonem DOMu.
3. Poslední fallback: bez screenshotu (odeslání reportu s DOM snapshotem a logy), aby akce nikdy neselhávala tvrdou chybou.

## Konfigurace
- `config/feedback.php`
  - `screenshot.strategy`: `auto|playwright|html2canvas|none` (výchozí `auto`).
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
  - Strategie `auto`/`playwright`: 1) pokus o server-side screenshot, 2) při selhání fallback na html2canvas nad sanitizovaným DOM (odstranění CORS stylesheetů, náhrada `oklab/oklch`), 3) bez screenshotu.
  - Záznam konzolových logů je stručný (prefx `[FB]`).

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
