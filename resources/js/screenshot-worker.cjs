'use strict';

const { chromium } = require('playwright');

function parseArgs(argv) {
  const out = {};
  for (const arg of argv.slice(2)) {
    const [k, v] = arg.split('=');
    const key = k.replace(/^--/, '');
    out[key] = v === undefined ? true : v;
  }
  return out;
}

(async () => {
  const args = parseArgs(process.argv);
  const url = args.url;
  const selector = args.selector || '#snapshot-root';
  const outPath = args.out || 'out.png';
  const width = parseInt(args.width || '1728', 10);
  const height = parseInt(args.height || '919', 10);
  const dpr = parseFloat(args.dpr || '2');
  const fullPage = String(args.fullPage || 'false') === 'true';

  if (!url) {
    console.error(JSON.stringify({ ok: false, error: 'Missing --url' }));
    process.exit(2);
  }

  const browser = await chromium.launch({ headless: true });
  try {
    const context = await browser.newContext({ viewport: { width, height }, deviceScaleFactor: dpr });
    const page = await context.newPage();

    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
    // Počkat na načtení všech fontů
    try { await page.evaluate(() => document.fonts.ready); } catch (e) {}
    // Další stabilizace sítě
    try { await page.waitForLoadState('networkidle', { timeout: 10000 }); } catch (e) {}

    let buffer;
    if (fullPage) {
      buffer = await page.screenshot({ path: outPath, type: 'png', fullPage: true });
    } else {
      const el = await page.waitForSelector(selector, { timeout: 10000 });
      const box = await el.boundingBox();
      buffer = await el.screenshot({ path: outPath, type: 'png' });
    }

    const size = await page.evaluate(() => ({
      w: document.documentElement.clientWidth,
      h: document.documentElement.clientHeight,
    }));

    console.log(JSON.stringify({ ok: true, path: outPath, width: size.w, height: size.h, mime: 'image/png' }));
    await browser.close();
    process.exit(0);
  } catch (e) {
    try { await browser.close(); } catch (_) {}
    console.error(JSON.stringify({ ok: false, error: e.message }));
    process.exit(1);
  }
})();
