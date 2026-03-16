import domtoimage from 'dom-to-image-more';
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

            window.addEventListener('unhandledrejection', (e) => {
                if (this.errors) {
                    this.errors.push({
                        type: 'promise-rejection',
                        reason: this.safeStringify(e.reason),
                        timestamp: new Date().toISOString()
                    });
                }
            });
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
                    this.logNetworkFailure(args[0], args[1]?.method || 'GET', 'EXCEPTION', Date.now() - start, error.message);
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

            try {
                let dataUrl = '';
                const options = {
                    quality: 0.8,
                    bgcolor: '#ffffff',
                    filter: (node) => {
                        if (node.id === 'ks-fb-root' || node.dataset?.html2canvasIgnore === 'true') return false;
                        if (this.options.maskSensitive && (node.classList?.contains('bugmask') || node.dataset?.bugmask === 'true')) return false;
                        if (node.tagName === 'INPUT' && node.type === 'password') return false;
                        return true;
                    }
                };

                // Získání referencí na knihovny z window (pro jistotu, pokud nejsou v lokálním scope)
                const domToImg = window.domtoimage || (typeof domtoimage !== 'undefined' ? domtoimage : null);
                const h2c = window.html2canvas || (typeof html2canvas !== 'undefined' ? html2canvas : null);

                // First try: dom-to-image
                if (domToImg) {
                    try {
                        console.log('Attempting screenshot with dom-to-image-more...');
                        dataUrl = await domToImg.toJpeg(document.body, {
                            ...options,
                            copyStyles: true,
                            discoverCheck: false, // Disable discoverCheck to avoid some CORS issues
                            cacheBust: true, // Try to bypass some cache/CORS issues
                            errorHandler: (err) => {
                                console.warn('dom-to-image error caught (continuing):', err);
                            }
                        });
                        console.log('Screenshot successful (dom-to-image)');
                    } catch (domErr) {
                        console.warn('dom-to-image-more failed, trying html2canvas...', domErr);
                        if (h2c) {
                            const canvas = await h2c(document.body, {
                                useCORS: true,
                                allowTaint: true,
                                scale: Math.min(window.devicePixelRatio, 2),
                                ignoreElements: (el) => el.dataset.html2canvasIgnore === 'true' || el.classList.contains('bugmask') || el.dataset.bugmask === 'true',
                                onclone: (clonedDoc) => {
                                    // Fix for Tailwind v4 oklab() colors which html2canvas doesn't support
                                    const styleElements = clonedDoc.getElementsByTagName('style');
                                    for (let style of styleElements) {
                                        if (style.innerHTML.includes('oklab')) {
                                            style.innerHTML = style.innerHTML.replace(/oklab\([^)]+\)/g, 'transparent');
                                        }
                                    }
                                    // Also fix inline styles
                                    const allElements = clonedDoc.getElementsByTagName('*');
                                    for (let el of allElements) {
                                        if (el.style && el.style.cssText && el.style.cssText.includes('oklab')) {
                                            el.style.cssText = el.style.cssText.replace(/oklab\([^)]+\)/g, 'transparent');
                                        }
                                    }
                                }
                            });
                            dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                            console.log('Screenshot successful (html2canvas fallback)');
                        } else {
                            throw domErr;
                        }
                    }
                } else if (h2c) {
                    // Only html2canvas available
                    console.log('Attempting screenshot with html2canvas (standalone)...');
                    const canvas = await h2c(document.body, {
                        useCORS: true,
                        allowTaint: true,
                        scale: Math.min(window.devicePixelRatio, 2),
                        ignoreElements: (el) => el.dataset.html2canvasIgnore === 'true' || el.classList.contains('bugmask') || el.dataset.bugmask === 'true',
                        onclone: (clonedDoc) => {
                            const styleElements = clonedDoc.getElementsByTagName('style');
                            for (let style of styleElements) {
                                if (style.innerHTML.includes('oklab')) {
                                    style.innerHTML = style.innerHTML.replace(/oklab\([^)]+\)/g, 'transparent');
                                }
                            }
                        }
                    });
                    dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                    console.log('Screenshot successful (html2canvas)');
                } else {
                    console.error('No screenshot library (domtoimage or html2canvas) found!');
                }

                widgetEl.style.display = originalDisplay;
                return dataUrl;
            } catch (e) {
                console.error('Screenshot failed completely', e);
                widgetEl.style.display = originalDisplay;
                return null;
            }
        },

        async submitFeedback() {
            if (!this.form.title || !this.form.description) return;
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
