'use strict';

const path = require('path');

function robustRequire(name) {
    try {
        return require(name);
    } catch (e) {
        try {
            // Zkusíme najít v node_modules relativně k CWD
            const localPath = path.resolve(process.cwd(), 'node_modules', name);
            return require(localPath);
        } catch (e2) {
            // Zkusíme najít v node_modules relativně k scriptu
            try {
                const scriptNodeModules = path.resolve(__dirname, '..', '..', 'node_modules', name);
                return require(scriptNodeModules);
            } catch (e3) {
                // Poslední pokus: zkusíme require.resolve pro lepší diagnostiku
                try {
                    const resolved = require.resolve(name, { paths: [process.cwd(), __dirname, path.join(process.cwd(), 'node_modules')] });
                    return require(resolved);
                } catch (e4) {
                    throw e; // Vyhodíme původní chybu
                }
            }
        }
    }
}

let playwright;
try {
  playwright = robustRequire('playwright');
} catch (e) {
  try {
    playwright = robustRequire('playwright-core');
  } catch (e2) {
    try {
      playwright = robustRequire('playwright-chromium');
    } catch (e3) {
      console.error(JSON.stringify({
        ok: false,
        error: 'Could not load playwright, playwright-core or playwright-chromium',
        details: e.message,
        stack: e.stack,
        node_path: process.env.NODE_PATH,
        module_paths: module.paths,
        cwd: process.cwd(),
        __dirname: __dirname
      }));
      process.exit(1);
    }
  }
}

const { chromium } = playwright;

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
  const executablePath = args.executablePath || null;

  if (!url) {
    console.error(JSON.stringify({ ok: false, error: 'Missing --url', diagnostics: { cwd: process.cwd(), env: !!process.env.NODE_PATH } }));
    process.exit(2);
  }

  const launchOptions = {
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
  };

  if (executablePath) {
    launchOptions.executablePath = executablePath;
  }

  const startTs = Date.now();
  const diagnostics = {
    playwrightFound: !!playwright,
    nodeVersion: process.version,
    platform: process.platform,
    cwd: process.cwd(),
    executablePath: executablePath
  };

  let browser;
  try {
    browser = await chromium.launch(launchOptions);
  } catch (e) {
    console.error(JSON.stringify({
      ok: false,
      error: 'Browser launch failed: ' + e.message,
      diagnostics: diagnostics,
      stack: e.stack
    }));
    process.exit(1);
  }

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
      if (!el) throw new Error('Selector not found: ' + selector);
      buffer = await el.screenshot({ path: outPath, type: 'png' });
    }

    const size = await page.evaluate(() => ({
      w: document.documentElement.clientWidth,
      h: document.documentElement.clientHeight,
    }));

    console.log(JSON.stringify({
      ok: true,
      driver: 'playwright',
      path: outPath,
      width: size.w,
      height: size.h,
      mime: 'image/png',
      durationMs: Date.now() - startTs,
      diagnostics: diagnostics
    }));
    await browser.close();
    process.exit(0);
  } catch (e) {
    if (browser) try { await browser.close(); } catch (_) {}
    console.error(JSON.stringify({
      ok: false,
      error: e.message,
      durationMs: Date.now() - startTs,
      diagnostics: diagnostics,
      stack: e.stack
    }));
    process.exit(1);
  }
})();
