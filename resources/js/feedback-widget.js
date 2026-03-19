import html2canvas from 'html2canvas';

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
    if (!window.Alpine) return;
    if (window.Alpine.data('ksFeedbackWidget')) return;

    window.ksFeedbackWidgetRegistered = true;
    window.Alpine.data('ksFeedbackWidget', () => ({
        isOpen: false,
        submitting: false,
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
            document.body.style.overflow = 'hidden';
        },

        closeModal() {
            this.isOpen = false;
            document.body.style.overflow = '';
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
            const main = document.querySelector('main') || document.body;
            let html = main.outerHTML;

            // Sanitize
            html = html.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
            // Mask inputs
            html = html.replace(/value="[^"]*"/gi, 'value="[redacted]"');

            return html.substring(0, 102400); // 100KB limit
        },

        async captureScreenshot() {
            const widgetEl = document.getElementById('ks-fb-root');
            const originalDisplay = widgetEl.style.display;
            widgetEl.style.display = 'none';

            const cfg = window.KS_FEEDBACK_CONFIG || { strategy: 'auto', playwright: { enabled: false }, endpoints: {} };
            const strategy = cfg.strategy || 'auto';
            const startTs = performance.now();
            console.log('[FB] captureScreenshot start | strategy =', strategy);

            const restore = () => { widgetEl.style.display = originalDisplay; };

            const buildDomSnapshot = () => {
                // Zachytit relevantní CSS
                let consolidatedCss = '';
                const styleSheets = Array.from(document.styleSheets);

                for (let sheet of styleSheets) {
                    try {
                        const isSameOrigin = !sheet.href || sheet.href.includes(window.location.hostname) || sheet.href.startsWith('/') || sheet.href.includes('localhost');
                        if (!isSameOrigin) continue;

                        const rules = sheet.cssRules || sheet.rules;
                        if (!rules) continue;

                        let sheetCss = '';
                        for (let j = 0; j < rules.length; j++) {
                            sheetCss += rules[j].cssText + '\n';
                        }

                        // Clean oklab/oklch from this batch
                        if (/(oklab|oklch)\s*\([^)]+\)/i.test(sheetCss)) {
                            sheetCss = sheetCss.replace(/(oklab|oklch)\s*\([^)]+\)/gi, 'transparent');
                        }
                        consolidatedCss += sheetCss;
                    } catch (e) {
                        // console.warn('[FB] Could not read stylesheet', sheet.href, e);
                    }
                }

                const headHtml = `<style class="ks-feedback-consolidated-css">${consolidatedCss}</style>`;

                // Zachytit kompletní body, ale bez skriptů
                const bodyClone = document.body.cloneNode(true);

                // Odstranit skripty, widget, overlay a další věci z klonu
                const toRemove = bodyClone.querySelectorAll('script, #ks-fb-root, .ks-fb-overlay, .ks-fab-trigger');
                toRemove.forEach(el => el.remove());

                // Sanitize bodyClone inline styles (pro jistotu i pro server)
                bodyClone.querySelectorAll('*').forEach(el => {
                    if (el.hasAttribute && el.hasAttribute('style')) {
                        const s = el.getAttribute('style');
                        if (s && /(oklab|oklch)\s*\([^)]+\)/i.test(s)) {
                            el.setAttribute('style', s.replace(/(oklab|oklch)\s*\([^)]+\)/gi, 'transparent'));
                        }
                    }
                    // SVG attributes
                    if (el.hasAttribute && el.hasAttribute('fill') && /(oklab|oklch)/i.test(el.getAttribute('fill'))) {
                        el.setAttribute('fill', 'transparent');
                    }
                    if (el.hasAttribute && el.hasAttribute('stroke') && /(oklab|oklch)/i.test(el.getAttribute('stroke'))) {
                        el.setAttribute('stroke', 'transparent');
                    }
                    if (el.style && el.style.backgroundImage && /(oklab|oklch)\s*\([^)]+\)/i.test(el.style.backgroundImage)) {
                        el.style.backgroundImage = 'none';
                    }
                });

                let html = bodyClone.innerHTML;
                html = html.replace(/value="[^"]*"/gi, 'value="[redacted]"');

                return {
                    dom: html.substring(0, 1048576), // Zvýšeno na 1MB
                    head: headHtml,
                    bodyClass: document.body.className,
                    bodyStyle: document.body.style.cssText,
                    htmlClass: document.documentElement.className,
                };
            };

            const sanitizeClone = (clonedDoc) => {
                try {
                    // 1. Remove ALL external stylesheets to prevent html2canvas from trying to parse them
                    const links = Array.from(clonedDoc.getElementsByTagName('link'));
                    for (let link of links) {
                        if (link.rel === 'stylesheet') link.remove();
                    }

                    // 1b. Consolidate and clean CSS from original document and inject it
                    let consolidatedCss = '';
                    try {
                        const styleSheets = Array.from(document.styleSheets);
                        for (let sheet of styleSheets) {
                            try {
                                const isSameOrigin = !sheet.href || sheet.href.includes(window.location.hostname) || sheet.href.startsWith('/') || sheet.href.includes('localhost');
                                if (!isSameOrigin) continue;
                                const rules = sheet.cssRules || sheet.rules;
                                if (!rules) continue;
                                for (let j = 0; j < rules.length; j++) {
                                    consolidatedCss += rules[j].cssText + '\n';
                                }
                            } catch (e) {}
                        }
                        if (/(oklab|oklch)\s*\([^)]+\)/i.test(consolidatedCss)) {
                            consolidatedCss = consolidatedCss.replace(/(oklab|oklch)\s*\([^)]+\)/gi, 'transparent');
                        }
                        const consolidatedStyle = clonedDoc.createElement('style');
                        consolidatedStyle.className = 'ks-fb-consolidated';
                        consolidatedStyle.textContent = consolidatedCss;
                        clonedDoc.head.appendChild(consolidatedStyle);
                    } catch (e) {}

                    // 2. Add global override for Tailwind v4 variables and known crashers
                    const override = clonedDoc.createElement('style');
                    override.innerHTML = `
                        :root, * {
                            --tw-ring-color: transparent !important;
                            --tw-ring-offset-color: transparent !important;
                            --tw-shadow-color: transparent !important;
                            --tw-outline-color: transparent !important;
                            --tw-bg-color: transparent !important;
                            --tw-text-color: inherit !important;
                            /* Reset common TW v4 variables that might still contain oklab even if we missed some */
                            --tw-gradient-from: transparent !important;
                            --tw-gradient-to: transparent !important;
                            --tw-gradient-stops: transparent !important;
                        }
                        /* Disable animations/transitions */
                        *, *::before, *::after {
                            animation: none !important;
                            transition: none !important;
                        }
                    `;
                    clonedDoc.head.appendChild(override);

                    // 3. Clean all style elements
                    const styleElements = Array.from(clonedDoc.getElementsByTagName('style'));
                    for (let style of styleElements) {
                        if (style === override) continue;
                        if (/(oklab|oklch)/i.test(style.innerHTML)) {
                            // Replace function call with transparent, even if multi-line
                            style.innerHTML = style.innerHTML.replace(/(oklab|oklch)\s*\([^)]+\)/gi, 'transparent');
                        }
                    }

                    // 4. Clean all inline styles
                    const allElements = Array.from(clonedDoc.getElementsByTagName('*'));
                    let cleanedInline = 0;
                    for (let el of allElements) {
                        if (el.hasAttribute && el.hasAttribute('style')) {
                            const s = el.getAttribute('style');
                            if (s && /(oklab|oklch)/i.test(s)) {
                                el.setAttribute('style', s.replace(/(oklab|oklch)\s*\([^)]+\)/gi, 'transparent'));
                                cleanedInline++;
                            }
                        }
                        // SVG attributes
                        if (el.hasAttribute && el.hasAttribute('fill') && /(oklab|oklch)/i.test(el.getAttribute('fill'))) {
                            el.setAttribute('fill', 'transparent');
                        }
                        if (el.hasAttribute && el.hasAttribute('stroke') && /(oklab|oklch)/i.test(el.getAttribute('stroke'))) {
                            el.setAttribute('stroke', 'transparent');
                        }
                        // Also check for background-image with gradients that might contain oklab
                        if (el.style && el.style.backgroundImage && /(oklab|oklch)/i.test(el.style.backgroundImage)) {
                            el.style.backgroundImage = 'none';
                        }
                    }

                    // 5. Aggressively clean accessible stylesheets (rules) rekurzivně
                    let cleanedRules = 0;
                    const colorRegexTest = /(oklab|oklch)\s*\([^)]+\)/i;
                    const cleanRule = (rule) => {
                        try {
                            if (rule.style) {
                                let changed = false;
                                if (colorRegexTest.test(rule.style.cssText)) {
                                    for (let k = 0; k < rule.style.length; k++) {
                                        const prop = rule.style[k];
                                        const val = rule.style.getPropertyValue(prop);
                                        if (colorRegexTest.test(val)) {
                                            rule.style.setProperty(prop, 'transparent', 'important');
                                            changed = true;
                                        }
                                    }
                                    if (changed) cleanedRules++;
                                }
                            }
                            const subRules = rule.cssRules || rule.rules;
                            if (subRules) {
                                for (let j = 0; j < subRules.length; j++) {
                                    cleanRule(subRules[j]);
                                }
                            }
                        } catch (e) {}
                    };

                    for (let i = 0; i < clonedDoc.styleSheets.length; i++) {
                        const sheet = clonedDoc.styleSheets[i];
                        try {
                            const rules = sheet.cssRules || sheet.rules;
                            if (!rules) {
                                sheet.disabled = true;
                                continue;
                            }
                            for (let j = 0; j < rules.length; j++) {
                                cleanRule(rules[j]);
                            }
                        } catch (e) {
                            try { sheet.disabled = true; } catch(err) {}
                        }
                    }
                    console.log(`[FB] sanitizeClone: cleaned ${cleanedInline} inline, ${cleanedRules} rules.`);
                } catch (e) { console.warn('[FB] sanitizeClone error', e); }
            };

            const html2canvasFallback = async () => {
                const h2c = window.html2canvas || (typeof html2canvas !== 'undefined' ? html2canvas : null);
                if (!h2c) return null;
                console.log('[FB] html2canvas fallback start');
                try {
                    const canvas = await h2c(document.body, {
                        useCORS: true,
                        allowTaint: true,
                        logging: true,
                        scale: Math.min(window.devicePixelRatio, 2),
                        ignoreElements: (el) => el.dataset.html2canvasIgnore === 'true' || el.classList.contains('bugmask') || el.dataset.bugmask === 'true',
                        onclone: sanitizeClone,
                    });
                    console.log('[FB] html2canvas fallback done');
                    return canvas.toDataURL('image/jpeg', 0.8);
                } catch (e) {
                    console.warn('[FB] html2canvas fallback failed', e.message);
                    return null;
                }
            };

            try {
                let dataUrl = null;
                // 1) Primárně Playwright (server)
                if ((strategy === 'auto' || strategy === 'playwright') && cfg.playwright?.enabled && cfg.endpoints?.serverScreenshot) {
                    try {
                        console.log('[FB] server screenshot attempt');
                        const snap = buildDomSnapshot();
                        const res = await fetch(cfg.endpoints.serverScreenshot, {
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
                        const out = await res.json();
                        if (out.ok && out.image) {
                            console.log('[FB] server screenshot ok');
                            dataUrl = out.image;
                        } else {
                            console.warn('[FB] server screenshot failed, status', res.status, out?.message || out?.error);
                        }
                    } catch (e) {
                        console.warn('[FB] server screenshot exception', e.message);
                    }
                }

                // 2) Fallback html2canvas
                if (!dataUrl && strategy !== 'playwright') {
                    dataUrl = await html2canvasFallback();
                }

                // 3) Last resort: no screenshot
                restore();
                console.log('[FB] captureScreenshot end | ms =', Math.round(performance.now() - startTs));
                return dataUrl;
            } catch (e) {
                console.error('[FB] captureScreenshot fatal', e);
                restore();
                return null;
            }
        },

        async submitFeedback() {
            if (this.submitting || !this.form.title || !this.form.description) return;
            this.submitting = true;

            try {
                const screenshot = this.options.screenshot ? await this.captureScreenshot() : null;
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
                        screenshot: screenshot,
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
                    // Non-JSON response (likely a redirect or server error page)
                    const text = await response.text();
                    console.error('Server returned non-JSON response:', text.substring(0, 500));
                    throw new Error(`Server returned status ${response.status} with non-JSON content.`);
                }

                if (response.ok) {
                    this.showStatus(result.message || 'Feedback byl úspěšně odeslán.', 'success');
                    this.resetForm();
                    this.closeModal();
                } else {
                    this.showStatus(result.message || result.error || 'Chyba při odesílání.', 'error');
                }
            } catch (e) {
                this.showStatus('Došlo k neočekávané chybě: ' + (e.message || 'Neznámá chyba'), 'error');
                console.error(e);
            } finally {
                this.submitting = false;
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
}

// Inicializace
if (window.Alpine) {
    registerKsFeedbackWidget();
} else {
    document.addEventListener('alpine:init', registerKsFeedbackWidget);
}
