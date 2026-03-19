import html2canvas from 'html2canvas';
import * as htmlToImage from 'html-to-image';

/**
 * RING BUFFER
 */
class RingBuffer {
    constructor(maxSize) {
        this.maxSize = maxSize;
        this.buffer = [];
    }
    push(item) {
        this.buffer.push(item);
        if (this.buffer.length > this.maxSize) {
            this.buffer.shift();
        }
    }
    toArray() {
        return [...this.buffer];
    }
    get length() {
        return this.buffer.length;
    }
}

/**
 * KBELSTÍ SOKOLI - FEEDBACK WIDGET
 */
function registerKsFeedbackWidget() {
    if (!window.Alpine) {
        console.debug('[FB] Alpine not ready yet for registration');
        return;
    }

    if (window.ksFeedbackWidgetRegistered) {
        console.debug('[FB] ksFeedbackWidget already registered.');
        return;
    }

    console.log('[FB] Registering Alpine component...');
    const ScreenshotService = {
        logs: [],
        addLog(msg) { this.logs.push(`[${new Date().toISOString().split('T')[1].split('.')[0]}] ${msg}`); },

        async capture(options = {}) {
            const startTs = performance.now();
            this.logs = [];
            this.addLog(`[JS] Capture start | strategy = ${options.strategy || 'auto'}`);

            const config = window.KS_FEEDBACK_CONFIG || { strategy: 'playwright', playwright: { enabled: false }, endpoints: {} };
            const strategy = options.strategy || config.strategy || 'playwright';
            const targetSelector = options.targetSelector || 'body';

            let result = { ok: false, durationMs: 0, strategy: 'none', logs: this.logs };

            try {
                // 1. Try Server Screenshot (Playwright) - Main Strategy
                if (strategy === 'playwright' || strategy === 'server' || strategy === 'auto') {
                    if (config.endpoints?.serverScreenshot) {
                        this.addLog('[JS] Attempting server-side screenshot (Playwright)');
                        const serverRes = await this.tryServer(config.endpoints.serverScreenshot);
                        if (serverRes.ok) {
                            this.addLog('[JS] Server screenshot success');
                            result = { ...serverRes, strategy: 'server' };
                        } else {
                            this.addLog(`[JS] Server screenshot failed: ${serverRes.error?.message || 'unknown'}`);
                        }
                    } else {
                        this.addLog('[JS] Server screenshot skipped (no endpoint)');
                    }
                }

                // 2. Fallback to client-side only if server failed and allowed
                if (!result.ok && (strategy === 'auto' || strategy === 'client')) {
                    this.addLog('[JS] Attempting client-side fallback (html-to-image)');
                    const htiRes = await this.tryHtmlToImage(targetSelector);
                    if (htiRes.ok) {
                        this.addLog('[JS] Client fallback success');
                        result = { ...htiRes, strategy: 'client-html-to-image' };
                    } else {
                        this.addLog(`[JS] Client fallback failed: ${htiRes.error?.message || 'unknown'}`);
                    }
                }

            } catch (e) {
                this.addLog(`[JS] Fatal capture error: ${e.message}`);
                result.error = { code: 'FATAL', message: e.message };
            } finally {
                result.durationMs = Math.round(performance.now() - startTs);
                result.logs = this.logs;
            }

            return result;
        },

        async tryServer(endpoint) {
            try {
                // Server supports modern CSS, no need to sanitize!
                const snap = this.buildDomSnapshot(false);
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        dom: snap.dom,
                        head: snap.head,
                        bodyClass: snap.bodyClass,
                        bodyStyle: snap.bodyStyle,
                        htmlClass: snap.htmlClass,
                        viewport: { width: window.innerWidth, height: window.innerHeight },
                        dpr: Math.min(window.devicePixelRatio || 1, 2),
                        fullPage: false,
                    })
                });

                if (!res.ok) {
                    const errorData = await res.json().catch(() => ({}));
                    return { ok: false, error: { code: errorData.code || 'HTTP_' + res.status, message: errorData.message || res.statusText } };
                }

                const out = await res.json();
                if (out.ok && out.image) {
                    return { ok: true, base64: out.image, width: out.width, height: out.height, mime: out.mime || 'image/png' };
                }
                return { ok: false, error: { code: out.code || 'SERVER_ERROR', message: out.message || 'Worker failed' } };
            } catch (e) {
                return { ok: false, error: { code: 'EXCEPTION', message: e.message } };
            }
        },

        isSameOrigin(href) {
            if (!href) return true;
            try {
                const url = new URL(href, window.location.origin);
                return url.origin === window.location.origin;
            } catch (e) {
                return href.startsWith('/') || href.startsWith('./') || href.startsWith('../');
            }
        },

        buildDomSnapshot(shouldSanitize = true) {
            // Zachytit relevantní CSS
            let consolidatedCss = '';
            const styleSheets = Array.from(document.styleSheets);

            for (let sheet of styleSheets) {
                try {
                    if (sheet.href && !this.isSameOrigin(sheet.href)) {
                        continue;
                    }

                    const rules = sheet.cssRules || sheet.rules;
                    if (!rules) continue;

                    let sheetCss = '';
                    for (let j = 0; j < rules.length; j++) {
                        try {
                            sheetCss += rules[j].cssText + '\n';
                        } catch (re) {}
                    }

                    // Clean modern colors from CSS only if required
                    consolidatedCss += shouldSanitize ? this.sanitizeCssString(sheetCss) : sheetCss;
                } catch (e) {}
            }

            // Cache for potential reuse in fallback
            this._lastConsolidatedCss = consolidatedCss;

            const headHtml = `<style class="ks-feedback-consolidated-css">${consolidatedCss}</style>`;

            // Zachytit body klon
            const bodyClone = document.body.cloneNode(true);
            const toRemove = bodyClone.querySelectorAll('script, #ks-fb-root, .ks-fb-overlay, .ks-fab-trigger');
            toRemove.forEach(el => el.remove());

            if (shouldSanitize) {
                this.sanitizeElementRecursively(bodyClone);
            }

            let html = bodyClone.innerHTML;
            html = html.replace(/value="[^"]*"/gi, 'value="[redacted]"');

            return {
                dom: html.substring(0, 1048576), // 1MB limit
                head: headHtml,
                bodyClass: document.body.className,
                bodyStyle: document.body.style.cssText,
                htmlClass: document.documentElement.className,
            };
        },

        async tryHtml2Canvas(selector) {
            const h2c = window.html2canvas || (typeof html2canvas !== 'undefined' ? html2canvas : null);
            if (!h2c) return { ok: false, error: { code: 'LIBRARY_MISSING', message: 'html2canvas not loaded' } };

            try {
                // For html2canvas we ALWAYS need a clean DOM
                const snap = this.buildDomSnapshot(true);
                const target = document.querySelector(selector) || document.body;
                const canvas = await h2c(target, {
                    useCORS: true,
                    allowTaint: true,
                    logging: false,
                    scale: Math.min(window.devicePixelRatio || 1, 2),
                    ignoreElements: (el) => el.dataset.html2canvasIgnore === 'true' || el.classList.contains('bugmask') || el.dataset.bugmask === 'true',
                    onclone: (clonedDoc) => {
                        this.sanitizeClonedDocument(clonedDoc);
                    },
                });
                return { ok: true, base64: canvas.toDataURL('image/jpeg', 0.8), mime: 'image/jpeg', width: canvas.width, height: canvas.height };
            } catch (e) {
                return { ok: false, error: { code: 'H2C_ERROR', message: e.message } };
            }
        },

        async tryHtmlToImage(selector) {
            if (!htmlToImage) return { ok: false, error: { code: 'LIBRARY_MISSING', message: 'html-to-image not loaded' } };

            try {
                const target = document.querySelector(selector) || document.body;

                // html-to-image takes options to filter elements
                const options = {
                    filter: (node) => {
                        if (node.dataset && node.dataset.html2canvasIgnore === 'true') return false;
                        if (node.classList && node.classList.contains('ks-fb-root')) return false; // self protection
                        return true;
                    },
                    pixelRatio: Math.min(window.devicePixelRatio || 1, 2),
                    backgroundColor: window.getComputedStyle(document.body).backgroundColor || '#ffffff',
                };

                // html-to-image uses SVG foreignObject which is usually better with modern CSS,
                // but we still sanitize just in case.
                const dataUrl = await htmlToImage.toJpeg(target, options);
                return { ok: true, base64: dataUrl, mime: 'image/jpeg' };
            } catch (e) {
                return { ok: false, error: { code: 'HTI_ERROR', message: e.message } };
            }
        },

        sanitizeCssString(css) {
            if (!css) return '';
            // Náhrada pro oklab/oklch/color-p3 za neutrální šedou, aby se předešlo pádu html2canvas
            // ale zachoval se aspoň nějaký vizuální prvek.
            return css.replace(/(oklab|oklch|color)\s*\((?:[^()]+|\([^()]*\))*\)/gi, 'rgb(120, 120, 120)');
        },

        sanitizeElementRecursively(rootEl) {
            const all = rootEl.querySelectorAll('*');
            all.forEach(el => {
                // Inline styles
                if (el.style) {
                    const properties = ['color', 'backgroundColor', 'borderColor', 'outlineColor', 'stopColor', 'fill', 'stroke'];
                    properties.forEach(prop => {
                        try {
                            const val = el.style[prop];
                            if (val && /(oklab|oklch|color)/i.test(val)) {
                                // Zkusíme se zeptat prohlížeče na computed barvu, pokud to jde
                                el.style[prop] = 'rgb(120, 120, 120)';
                            }
                        } catch (e) {}
                    });

                    if (el.style.backgroundImage && /(oklab|oklch|color)/i.test(el.style.backgroundImage)) {
                        el.style.backgroundImage = 'none'; // Background image s moderní barvou (gradient) raději pryč
                    }
                }

                // Pokud má element inline style atribut, vyčistíme ho i jako string
                if (el.hasAttribute && el.hasAttribute('style')) {
                    const s = el.getAttribute('style');
                    if (s && /(oklab|oklch|color)/i.test(s)) {
                        el.setAttribute('style', this.sanitizeCssString(s));
                    }
                }

                // SVG attributes
                ['fill', 'stroke'].forEach(attr => {
                    if (el.hasAttribute && el.hasAttribute(attr)) {
                        const val = el.getAttribute(attr);
                        if (val && /(oklab|oklch|color)/i.test(val)) {
                            el.setAttribute(attr, 'rgb(120, 120, 120)');
                        }
                    }
                });
            });
        },

        sanitizeClonedDocument(clonedDoc) {
            // 1. Keep important links (fonts, icons) but remove standard local styles
            // that we have consolidated and cleaned.
            Array.from(clonedDoc.querySelectorAll('link[rel="stylesheet"]')).forEach(el => {
                const href = el.getAttribute('href');
                if (href && (href.includes('fonts.googleapis') || href.includes('font-awesome'))) {
                    // Keep
                } else {
                    el.remove();
                }
            });
            Array.from(clonedDoc.querySelectorAll('style:not(.ks-feedback-consolidated-css)')).forEach(el => el.remove());

            // 2. Inject cleaned consolidated CSS from last capture attempt
            const consolidatedCss = this._lastConsolidatedCss || '';
            const style = clonedDoc.createElement('style');
            style.className = 'ks-feedback-consolidated-css';
            style.textContent = consolidatedCss + '\n' + `
                :root, * {
                    --tw-gradient-from: #888 !important;
                    --tw-gradient-to: #888 !important;
                    --tw-gradient-stops: #888 !important;
                    --tw-gradient-via: #888 !important;
                    animation: none !important;
                    transition: none !important;
                }
            `;
            clonedDoc.head.appendChild(style);

            // 3. Clean all elements (inline styles, SVGs)
            this.sanitizeElementRecursively(clonedDoc.body);
        }
    };

    window.Alpine.data('ksFeedbackWidget', () => ({
        isOpen: false,
        submitState: 'idle', // idle, validating, closing_modal, capturing_screenshot, submitting_report, success, failed
        // Ring Buffery inicializované v init()
        logs: null,
        errors: null,
        networkFailures: null,
        breadcrumbs: null,
        clicks: null,

        form: {
            type: 'bug',
            severity: 'medium',
            title: '',
            description: '',
            steps: '',
        },
        options: {
            screenshot: true,
            logs: true,
            network: true,
            performance: true,
            clicks: true,
            dom: true,
            maskSensitive: true
        },
        status: {
            show: false,
            message: '',
            type: 'success'
        },
        debugInfo: '',

        init() {
            // Inicializace Ring Bufferů
            this.logs = new RingBuffer(300);
            this.errors = new RingBuffer(100);
            this.networkFailures = new RingBuffer(100);
            this.breadcrumbs = new RingBuffer(50);
            this.clicks = new RingBuffer(200);

            this.setupLogging();
            this.setupErrorTracking();
            this.setupNetworkTracking();
            this.setupBreadcrumbs();
            this.setupClickTracking();

            this.debugInfo = `v${this.$el.dataset.appVersion || '1.0'}`;

            // ESC to close
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) this.closeModal();
            });

            // Pre-fill steps template
            this.resetForm();
        },

        openModal() {
            this.isOpen = true;
            this.submitState = 'idle';
            document.body.style.overflow = 'hidden';
        },

        async closeModal() {
            this.isOpen = false;
            document.body.style.overflow = '';
            // Počkat na dokončení Alpine.js transition (cca 200-300ms)
            return new Promise(r => setTimeout(r, 350));
        },

        safeStringify(obj, maxLen = 800) {
            try {
                const cache = new Set();
                let str = JSON.stringify(obj, (key, value) => {
                    if (typeof value === 'object' && value !== null) {
                        if (cache.has(value)) return '[Circular]';
                        cache.add(value);
                    }
                    if (value instanceof Error) {
                        return { message: value.message, stack: value.stack?.substring(0, 2000) };
                    }
                    return value;
                });
                if (str.length > maxLen) return str.substring(0, maxLen) + '... [truncated]';
                return str;
            } catch (e) {
                return '[unserializable]';
            }
        },

        setupLogging() {
            const originalConsole = {
                log: console.log, warn: console.warn, error: console.error, info: console.info, debug: console.debug
            };

            ['log', 'warn', 'error', 'info', 'debug'].forEach(level => {
                console[level] = (...args) => {
                    try {
                        if (this.logs) {
                            this.logs.push({
                                level,
                                timestamp: new Date().toISOString(),
                                message: args.map(arg => typeof arg === 'string' ? arg : this.safeStringify(arg)).join(' ')
                            });
                            // Force Alpine update
                            this.logs = this.logs;
                        }
                    } catch (e) {}
                    originalConsole[level].apply(console, args);
                };
            });
        },

        setupErrorTracking() {
            window.addEventListener('error', (e) => {
                if (this.errors) {
                    this.errors.push({
                        message: e.message,
                        filename: e.filename,
                        lineno: e.lineno,
                        colno: e.colno,
                        stack: e.error?.stack?.substring(0, 2000),
                        timestamp: new Date().toISOString()
                    });
                }
            });

            // Zachycení rejekcí pro logování do interního bufferu chyb.
            // Poznámka: Livewire 3 abort stavy jsou potlačeny globálním listenerem v <head>.
            window.addEventListener('unhandledrejection', (e) => {
                const reason = e.reason;

                // Opětovná kontrola pro jistotu (pokud by globální listener selhal nebo nebyl přítomen)
                const isLivewireAbort = !reason || (
                    typeof reason === 'object' &&
                    'status' in reason && reason.status === null &&
                    ('body' in reason || 'json' in reason || 'errors' in reason)
                );

                if (isLivewireAbort) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return;
                }

                if (this.errors) {
                    this.errors.push({
                        type: 'promise-rejection',
                        reason: this.safeStringify(reason),
                        timestamp: new Date().toISOString()
                    });
                }
            }, true);
        },

        setupNetworkTracking() {
            // Fetch wrap
            const originalFetch = window.fetch;
            window.fetch = async (...args) => {
                const start = Date.now();
                try {
                    const response = await originalFetch(...args);
                    if (!response.ok) {
                        this.logNetworkFailure(args[0], args[1]?.method || 'GET', response.status, Date.now() - start);
                    }
                    return response;
                } catch (error) {
                    // Ignorujeme AbortError (např. od Livewire při navigaci) v logování, ale vyhodíme ho dál
                    if (error.name !== 'AbortError') {
                        this.logNetworkFailure(args[0], args[1]?.method || 'GET', 'EXCEPTION', Date.now() - start, error.message);
                    }
                    throw error;
                }
            };

            // XHR wrap
            const originalXhrOpen = window.XMLHttpRequest.prototype.open;
            const originalXhrSend = window.XMLHttpRequest.prototype.send;

            window.XMLHttpRequest.prototype.open = function(method, url) {
                this._method = method;
                this._url = url;
                this._startTime = Date.now();
                return originalXhrOpen.apply(this, arguments);
            };

            window.XMLHttpRequest.prototype.send = function() {
                this.addEventListener('load', () => {
                    if (this.status >= 400) {
                        window.dispatchEvent(new CustomEvent('ks-network-failure', {
                            detail: { method: this._method, url: this._url, status: this.status, duration: Date.now() - this._startTime }
                        }));
                    }
                });
                this.addEventListener('error', () => {
                    window.dispatchEvent(new CustomEvent('ks-network-failure', {
                        detail: { method: this._method, url: this._url, status: 'EXCEPTION', duration: Date.now() - this._startTime }
                    }));
                });
                return originalXhrSend.apply(this, arguments);
            };

            window.addEventListener('ks-network-failure', (e) => {
                this.logNetworkFailure(e.detail.url, e.detail.method, e.detail.status, e.detail.duration);
            });
        },

        logNetworkFailure(url, method, status, duration, error = null) {
            try {
                if (!this.networkFailures) return;

                const parsedUrl = new URL(url, window.location.origin);
                // Redact query values
                parsedUrl.searchParams.forEach((val, key) => parsedUrl.searchParams.set(key, '[redacted]'));

                this.networkFailures.push({
                    method,
                    url: parsedUrl.toString(),
                    status,
                    duration_ms: duration,
                    error: error?.substring(0, 500),
                    timestamp: new Date().toISOString()
                });
                // Force Alpine update
                this.networkFailures = this.networkFailures;
            } catch (e) {
                // Fallback if URL is invalid
                if (this.networkFailures) {
                    this.networkFailures.push({ method, url: String(url).substring(0, 200), status, duration_ms: duration, timestamp: new Date().toISOString() });
                    this.networkFailures = this.networkFailures;
                }
            }
        },

        setupBreadcrumbs() {
            // Navigation
            window.addEventListener('popstate', () => {
                if (this.breadcrumbs) {
                    this.breadcrumbs.push({ type: 'nav', to: window.location.pathname, timestamp: new Date().toISOString() });
                }
            });

            // Scroll milestones
            let milestones = [25, 50, 75, 100];
            let reached = new Set();
            window.addEventListener('scroll', () => {
                const scrollPct = Math.round((window.scrollY + window.innerHeight) / document.documentElement.scrollHeight * 100);
                milestones.forEach(m => {
                    if (scrollPct >= m && !reached.has(m)) {
                        reached.add(m);
                        if (this.breadcrumbs) {
                            this.breadcrumbs.push({ type: 'scroll', depth: m + '%', timestamp: new Date().toISOString() });
                        }
                    }
                });
            }, { passive: true });

            // Interactions (delegated)
            document.addEventListener('submit', (e) => {
                if (this.breadcrumbs) {
                    this.breadcrumbs.push({ type: 'submit', form: e.target.id || e.target.className || 'unknown', timestamp: new Date().toISOString() });
                }
            }, true);
        },

        setupClickTracking() {
            document.addEventListener('click', (e) => {
                // Breadcrumb interaction (vždy)
                if (this.breadcrumbs && this.breadcrumbs.length < 50) {
                    const el = e.target.closest('button, a, input[type="submit"], [data-track]');
                    if (el) {
                        this.breadcrumbs.push({
                            type: 'click',
                            tag: el.tagName.toLowerCase(),
                            text: (el.innerText || el.ariaLabel || el.title || '').substring(0, 30).trim(),
                            timestamp: new Date().toISOString()
                        });
                    }
                }

                // Detailed click tracking (pokud zapnuto)
                if (!this.options.clicks || !this.clicks) return;

                const el = e.target;
                const descriptor = `${el.tagName.toLowerCase()}${el.id ? '#'+el.id : ''}${el.className ? '.'+el.className.split(' ').join('.').substring(0, 50) : ''}`;

                this.clicks.push({
                    x: e.clientX,
                    y: e.clientY,
                    element: descriptor.substring(0, 100),
                    text: (el.innerText || el.value || '').substring(0, 80).trim(),
                    timestamp: new Date().toISOString()
                });
                // Force Alpine update
                this.clicks = this.clicks;
            }, true);
        },

        getPerformanceContext() {
            const ctx = { nav: {}, slowResources: [], longTasks: { count: 0, top: [] } };

            // Navigation Timing
            const nav = performance.getEntriesByType('navigation')[0];
            if (nav) {
                ctx.nav = {
                    ttfb: Math.round(nav.responseStart - nav.startTime),
                    domReady: Math.round(nav.domContentLoadedEventEnd - nav.startTime),
                    load: Math.round(nav.loadEventEnd - nav.startTime),
                    type: nav.type
                };
            }

            // Slow resources (Top 20)
            ctx.slowResources = performance.getEntriesByType('resource')
                .sort((a, b) => b.duration - a.duration)
                .slice(0, 20)
                .map(r => ({ name: r.name.split('/').pop().substring(0, 100), type: r.initiatorType, duration: Math.round(r.duration) }));

            // Memory (Chrome only)
            if (performance.memory) {
                ctx.memory = {
                    limit: Math.round(performance.memory.jsHeapSizeLimit / 1048576) + 'MB',
                    used: Math.round(performance.memory.usedJSHeapSize / 1048576) + 'MB'
                };
            }

            return ctx;
        },

        getDomSnapshot() {
            if (!this.options.dom) return null;
            try {
                const snap = ScreenshotService.buildDomSnapshot();
                return snap.dom;
            } catch (e) {
                console.error('[FB] getDomSnapshot error', e);
                return null;
            }
        },

        async submitFeedback() {
            if (this.submitState !== 'idle' || !this.form.title || !this.form.description) return;

            this.submitState = 'validating';
            console.log('[FB] Submit started - validating');

            const config = window.KS_FEEDBACK_CONFIG || { screenshot_required: false, strategy: 'playwright' };
            const isScreenshotRequired = config.screenshot_required || false;

            try {
                // 1. Zavřít modal před pořízením screenshotu
                this.submitState = 'closing_modal';
                console.log('[FB] Closing modal for clean screenshot');
                await this.closeModal();

                // 2. Capture screenshot if enabled
                let screenshotResult = { ok: false };
                if (this.options.screenshot) {
                    this.submitState = 'capturing_screenshot';
                    console.log('[FB] Requesting server-side screenshot');

                    screenshotResult = await ScreenshotService.capture({
                        strategy: config.strategy || 'playwright',
                        targetSelector: 'body',
                        hideSelectorsBeforeCapture: ['#ks-fb-root', '.ks-fb-overlay', '.ks-fab-trigger']
                    });
                }

                if (this.options.screenshot && !screenshotResult.ok && isScreenshotRequired) {
                    this.submitState = 'failed';
                    this.showStatus('Screenshot je povinný, ale nepodařilo se jej pořídit.', 'error');
                    return;
                }

                this.submitState = 'submitting_report';
                console.log('[FB] Submitting report to backend');

                const domSnapshot = this.options.dom ? this.getDomSnapshot() : null;
                const perf = this.options.performance ? this.getPerformanceContext() : {};

                const payload = {
                    type: this.form.type,
                    severity: this.form.severity,
                    title: this.form.title,
                    description: this.form.description,
                    steps: this.form.steps,
                    include: this.options,
                    context: {
                        url: window.location.href,
                        route: this.$el.dataset.routeName || null,
                        referrer: document.referrer,
                        area: this.getSourceArea(),
                        locale: document.documentElement.lang || 'cs',
                        timestamp: new Date().toISOString(),
                        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                        appVersion: this.$el.dataset.appVersion || '1.0',
                        requestId: this.getResponseHeader('X-Request-ID'),
                        user: {
                            id: this.$el.dataset.userId || null,
                            email: this.$el.dataset.userEmail || null,
                            roles: (this.$el.dataset.userRoles || '').split(',').filter(Boolean)
                        },
                        device: {
                            userAgent: navigator.userAgent,
                            platform: navigator.platform,
                            viewport: { w: window.innerWidth, h: window.innerHeight, dpr: window.devicePixelRatio },
                            screen: { w: window.screen.width, h: window.screen.height, depth: window.screen.colorDepth },
                            connection: navigator.connection ? { type: navigator.connection.effectiveType, rtt: navigator.connection.rtt } : null
                        },
                        state: {
                            isOnline: navigator.onLine,
                            visibility: document.visibilityState,
                            detectedError: this.detectErrorOnPage()
                        }
                    },
                    capture: {
                        screenshot: screenshotResult.ok ? screenshotResult.base64 : null,
                        screenshot_meta: {
                            strategy: screenshotResult.strategy,
                            duration: screenshotResult.durationMs,
                            error: screenshotResult.error,
                            logs: screenshotResult.logs,
                            server_side: screenshotResult.strategy === 'server'
                        },
                        domLight: domSnapshot
                    },
                    logs: {
                        console: this.logs ? this.logs.toArray() : [],
                        errors: this.errors ? this.errors.toArray() : [],
                        network: this.networkFailures ? this.networkFailures.toArray() : [],
                        breadcrumbs: this.breadcrumbs ? this.breadcrumbs.toArray() : []
                    },
                    performance: perf
                };

                const response = await fetch('/feedback', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const contentType = response.headers.get('content-type');
                let result;

                if (contentType && contentType.includes('application/json')) {
                    result = await response.json();
                } else {
                    const text = await response.text();
                    console.error('[FB] Server returned non-JSON response:', text.substring(0, 500));
                    throw new Error(`Server returned status ${response.status} with non-JSON content.`);
                }

                if (response.ok) {
                    this.submitState = 'success';
                    console.log('[FB] Report submitted successfully');
                    this.showStatus(result.message || 'Feedback byl úspěšně odeslán.', 'success');
                    this.resetForm();
                } else {
                    this.submitState = 'failed';
                    this.showStatus(result.message || result.error || 'Chyba při odesílání.', 'error');
                    // Pokud selhalo odeslání, znovu otevřeme modal?
                    // Raději ne, aby se nepořizoval screenshot znovu, ale uživatel by měl mít šanci to opravit.
                    // Ale zadání říká "odeslat bug report", tak to necháme na failed stavu.
                }
            } catch (e) {
                this.submitState = 'failed';
                this.showStatus('Došlo k neočekávané chybě: ' + (e.message || 'Neznámá chyba'), 'error');
                console.error('[FB] Fatal error during submission:', e);
            }
        },

        detectErrorOnPage() {
            const errorEl = document.querySelector('.alert-danger, .toast-error, .text-danger, [data-error]');
            return errorEl ? errorEl.innerText.substring(0, 200).trim() : null;
        },

        getResponseHeader(name) {
            try {
                const perf = performance.getEntriesByType('navigation')[0];
                return null;
            } catch (e) { return null; }
        },

        getSourceArea() {
            const path = window.location.pathname;
            if (path.startsWith('/admin')) return 'admin';
            if (path.startsWith('/member')) return 'member';
            return 'public';
        },

        showStatus(message, type) {
            this.status = { show: true, message, type };
            setTimeout(() => { this.status.show = false; }, 5000);
        },

        resetForm() {
            this.form = {
                type: 'bug',
                severity: 'medium',
                title: '',
                description: '',
                steps: "1) Šel jsem na ...\n2) Klikl jsem na ...\n3) Očekával jsem ...\n4) Stalo se ..."
            };
        }
    }));

    window.ksFeedbackWidgetRegistered = true;
    console.log('[FB] Alpine component "ksFeedbackWidget" registered successfully.');
}

// Inicializace - zajistit, že Alpine.data() se volá okamžitě, i pokud je Alpine už načtené.
if (window.Alpine) {
    registerKsFeedbackWidget();
}

// Ale vždy posloucháme i na alpine:init pro případ, že se Alpine teprve inicializuje.
document.addEventListener('alpine:init', registerKsFeedbackWidget);

// Pro jistotu i na Livewire navigaci, pokud by Livewire čistil registry
document.addEventListener('livewire:navigated', registerKsFeedbackWidget);
