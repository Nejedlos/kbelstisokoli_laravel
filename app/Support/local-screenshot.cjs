const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const url = process.argv[2];
const outputPath = process.argv[3];
const options = JSON.parse(process.argv[4] || '{}');

(async () => {
    let browser;
    try {
        const launchOptions = {
            headless: true,
            args: ['--no-sandbox', '--disable-web-security', '--disable-setuid-sandbox']
        };

        // Použijeme zadaný Chrome pokud existuje
        if (options.executablePath && fs.existsSync(options.executablePath)) {
            launchOptions.executablePath = options.executablePath;
        }

        browser = await chromium.launch(launchOptions);
        const context = await browser.newContext({
            viewport: {
                width: options.width || 1280,
                height: options.height || 720
            },
            userAgent: options.userAgent || 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
            extraHTTPHeaders: options.headers || {}
        });

        const page = await context.newPage();

        // Navigace na URL s čekáním na síťový klid
        await page.goto(url, {
            waitUntil: options.waitUntil || 'networkidle',
            timeout: options.timeout || 30000
        });

        // Volitelně počkáme na selektor
        if (options.selector) {
            try {
                await page.waitForSelector(options.selector, { timeout: 5000 });
            } catch (e) {
                console.error(`Warning: Selector ${options.selector} not found within timeout.`);
            }
        }

        // Pokud je zadaný selektor pro výřez, vyfotíme jen ten
        const screenshotOptions = {
            path: outputPath,
            type: 'png'
        };

        if (options.selector) {
            const element = await page.$(options.selector);
            if (element) {
                await element.screenshot(screenshotOptions);
            } else {
                await page.screenshot(screenshotOptions);
            }
        } else {
            await page.screenshot(screenshotOptions);
        }

        console.log('SUCCESS');
    } catch (error) {
        console.error('ERROR:', error.message);
        process.exit(1);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
})();
