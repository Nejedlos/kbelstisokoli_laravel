<div id="ks-feedback-widget"
     x-data="ksFeedbackWidget()"
     x-init="init()"
     class="ks-feedback-system"
     data-html2canvas-ignore="true">

    <!-- Floating Action Button -->
    <button @click="openModal()"
            type="button"
            class="feedback-fab fixed z-[9999] bg-primary-600 hover:bg-primary-700 text-white rounded-full shadow-2xl transition-all duration-300 flex items-center justify-center group"
            :class="isOpen ? 'scale-0 opacity-0' : 'scale-100 opacity-100'"
            aria-label="Odeslat zpětnou vazbu">
        <i class="fa-light fa-bug text-2xl group-hover:rotate-12 transition-transform"></i>
        <span class="max-w-0 overflow-hidden group-hover:max-w-xs group-hover:ml-2 transition-all duration-500 ease-in-out whitespace-nowrap text-sm font-medium">
            Feedback
        </span>
    </button>

    <!-- Overlay -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeModal()"
         class="feedback-overlay fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[10000] flex items-center justify-center p-4">

        <!-- Modal -->
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             @click.stop
             class="feedback-modal bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col border border-slate-200">

            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-100 text-primary-600 rounded-xl flex items-center justify-center">
                        <i class="fa-light fa-comment-dots text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 leading-tight">Zpětná vazba</h3>
                        <p class="text-xs text-slate-500">Pomozte nám vylepšit Kbelstí Sokoli</p>
                    </div>
                </div>
                <button @click="closeModal()" type="button" class="text-slate-400 hover:text-slate-600 p-2 rounded-lg hover:bg-slate-100 transition-colors">
                    <i class="fa-light fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Body (Scrollable) -->
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                <form id="ks-feedback-form" @submit.prevent="submitFeedback()">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- Type -->
                        <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-slate-700">Typ hlášení <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="relative flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all"
                                       :class="form.type === 'bug' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-slate-100 bg-slate-50 text-slate-500 hover:bg-slate-100'">
                                    <input type="radio" x-model="form.type" value="bug" class="sr-only">
                                    <i class="fa-light fa-bug mb-1"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Bug</span>
                                </label>
                                <label class="relative flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all"
                                       :class="form.type === 'idea' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-slate-100 bg-slate-50 text-slate-500 hover:bg-slate-100'">
                                    <input type="radio" x-model="form.type" value="idea" class="sr-only">
                                    <i class="fa-light fa-lightbulb mb-1"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Nápad</span>
                                </label>
                                <label class="relative flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all"
                                       :class="form.type === 'feedback' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-slate-100 bg-slate-50 text-slate-500 hover:bg-slate-100'">
                                    <input type="radio" x-model="form.type" value="feedback" class="sr-only">
                                    <i class="fa-light fa-message-smile mb-1"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Ostatní</span>
                                </label>
                            </div>
                        </div>

                        <!-- Severity -->
                        <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-slate-700">Závažnost</label>
                            <select x-model="form.severity" class="w-full rounded-xl border-slate-200 text-sm focus:ring-primary-500 focus:border-primary-500">
                                <option value="low">Nízká</option>
                                <option value="medium">Střední</option>
                                <option value="high">Vysoká / Kritická</option>
                            </select>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="space-y-1.5 mb-4">
                        <label class="text-sm font-semibold text-slate-700">Nadpis <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.title" required maxlength="120" placeholder="Stručně popište problém..."
                               class="w-full rounded-xl border-slate-200 text-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <!-- Description -->
                    <div class="space-y-1.5 mb-4">
                        <label class="text-sm font-semibold text-slate-700">Popis <span class="text-red-500">*</span></label>
                        <textarea x-model="form.description" required rows="4" maxlength="5000" placeholder="Detailnější popis..."
                                  class="w-full rounded-xl border-slate-200 text-sm focus:ring-primary-500 focus:border-primary-500"></textarea>
                    </div>

                    <!-- Steps (Conditional for bugs) -->
                    <div x-show="form.type === 'bug'" x-collapse class="space-y-1.5 mb-4">
                        <label class="text-sm font-semibold text-slate-700">Kroky k reprodukci</label>
                        <textarea x-model="form.steps" rows="3" maxlength="10000" placeholder="1. Klikněte na... 2. Pak udělejte..."
                                  class="w-full rounded-xl border-slate-200 text-sm focus:ring-primary-500 focus:border-primary-500"></textarea>
                    </div>

                    <!-- Options -->
                    <div class="bg-slate-50 rounded-2xl p-4 mb-4">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Přiložit k hlášení</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" x-model="options.screenshot" class="rounded text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900">Aktuální screenshot</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" x-model="options.logs" class="rounded text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900">Logy konzole (<span x-text="logs.length"></span>)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" x-model="options.network" class="rounded text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900">Chyby sítě (<span x-text="networkFailures.length"></span>)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" x-model="options.performance" class="rounded text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900">Výkonnostní data</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" x-model="options.clicks" class="rounded text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900">Kliky (aktivovat)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" x-model="options.dom" class="rounded text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900">DOM Snapshot (struktura)</span>
                            </label>
                        </div>
                    </div>

                    <p class="text-[10px] text-slate-400 text-center">
                        Odesláním souhlasíte se sběrem diagnostických dat pro účely opravy chyb. Citlivá data jsou automaticky maskována.
                    </p>
                </form>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <span class="text-xs text-slate-400 font-mono" x-text="debugInfo"></span>
                <div class="flex gap-3">
                    <button @click="closeModal()" type="button" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">
                        Zrušit
                    </button>
                    <button @click="submitFeedback()" :disabled="submitting" type="button"
                            class="px-6 py-2 bg-primary-600 hover:bg-primary-700 disabled:bg-slate-300 text-white text-sm font-bold rounded-xl shadow-lg shadow-primary-500/30 transition-all flex items-center gap-2">
                        <template x-if="submitting">
                            <i class="fa-light fa-spinner fa-spin"></i>
                        </template>
                        <template x-if="!submitting">
                            <i class="fa-light fa-paper-plane"></i>
                        </template>
                        <span x-text="submitting ? 'Odesílám...' : 'Odeslat feedback'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Toast -->
    <template x-if="status.show">
        <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[10001] px-6 py-3 rounded-2xl shadow-2xl flex items-center gap-3 animate-bounce-in"
             :class="status.type === 'success' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white'">
            <i class="fa-light" :class="status.type === 'success' ? 'fa-check-circle' : 'fa-triangle-exclamation'"></i>
            <span class="text-sm font-bold" x-text="status.message"></span>
        </div>
    </template>

    <style>
        .feedback-fab {
            left: 24px;
            bottom: 24px;
            width: 48px;
            height: 48px;
        }
        @media (max-width: 640px) {
            .feedback-fab {
                left: 16px;
                bottom: calc(env(safe-area-inset-bottom, 0px) + 16px);
                width: 56px;
                height: 56px;
            }
        }
        .animate-bounce-in {
            animation: bounceIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        @keyframes bounceIn {
            0% { transform: translate(-50%, 100%) scale(0.5); opacity: 0; }
            100% { transform: translate(-50%, 0) scale(1); opacity: 1; }
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>

    <!-- Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/dom-to-image-more@2.9.5/dist/dom-to-image-more.min.js" integrity="sha256-6mFNCzL6D1p99rG+M9B9U+I1+6qQzYI0+G9Q9v+q08w=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <script>
        /**
         * RingBuffer pro efektivní ukládání logů s limitem.
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

        function ksFeedbackWidget() {
            return {
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
                    clicks: false,
                    dom: false,
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

                    this.debugInfo = `v${document.documentElement.dataset.appVersion || '1.0'}`;

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
                                this.logs.push({
                                    level,
                                    timestamp: new Date().toISOString(),
                                    message: args.map(arg => typeof arg === 'string' ? arg : this.safeStringify(arg)).join(' ')
                                });
                            } catch (e) {}
                            originalConsole[level].apply(console, args);
                        };
                    });
                },

                setupErrorTracking() {
                    window.addEventListener('error', (e) => {
                        this.errors.push({
                            message: e.message,
                            filename: e.filename,
                            lineno: e.lineno,
                            colno: e.colno,
                            stack: e.error?.stack?.substring(0, 2000),
                            timestamp: new Date().toISOString()
                        });
                    });

                    window.addEventListener('unhandledrejection', (e) => {
                        this.errors.push({
                            type: 'promise-rejection',
                            reason: this.safeStringify(e.reason),
                            timestamp: new Date().toISOString()
                        });
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
                    } catch (e) {
                        // Fallback if URL is invalid
                        this.networkFailures.push({ method, url: String(url).substring(0, 200), status, duration_ms: duration, timestamp: new Date().toISOString() });
                    }
                },

                setupBreadcrumbs() {
                    // Navigation
                    window.addEventListener('popstate', () => this.breadcrumbs.push({ type: 'nav', to: window.location.pathname, timestamp: new Date().toISOString() }));

                    // Scroll milestones
                    let milestones = [25, 50, 75, 100];
                    let reached = new Set();
                    window.addEventListener('scroll', () => {
                        const scrollPct = Math.round((window.scrollY + window.innerHeight) / document.documentElement.scrollHeight * 100);
                        milestones.forEach(m => {
                            if (scrollPct >= m && !reached.has(m)) {
                                reached.add(m);
                                this.breadcrumbs.push({ type: 'scroll', depth: m + '%', timestamp: new Date().toISOString() });
                            }
                        });
                    }, { passive: true });

                    // Interactions (delegated)
                    document.addEventListener('submit', (e) => {
                        this.breadcrumbs.push({ type: 'submit', form: e.target.id || e.target.className || 'unknown', timestamp: new Date().toISOString() });
                    }, true);
                },

                setupClickTracking() {
                    document.addEventListener('click', (e) => {
                        // Breadcrumb interaction (vždy)
                        if (this.breadcrumbs.length < 50) {
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
                        if (!this.options.clicks) return;

                        const el = e.target;
                        const descriptor = `${el.tagName.toLowerCase()}${el.id ? '#'+el.id : ''}${el.className ? '.'+el.className.split(' ').join('.') : ''}`;

                        this.clicks.push({
                            x: e.clientX,
                            y: e.clientY,
                            element: descriptor.substring(0, 100),
                            text: (el.innerText || el.value || '').substring(0, 80).trim(),
                            timestamp: new Date().toISOString()
                        });
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
                    const widgetEl = document.getElementById('ks-feedback-widget');
                    const originalDisplay = widgetEl.style.display;
                    widgetEl.style.display = 'none';

                    try {
                        let dataUrl = '';
                        const options = {
                            quality: 0.8,
                            bgcolor: '#ffffff',
                            filter: (node) => {
                                if (node.dataset?.html2canvasIgnore === 'true') return false;
                                if (this.options.maskSensitive && (node.classList?.contains('bugmask') || node.dataset?.bugmask === 'true')) return false;
                                if (node.tagName === 'INPUT' && node.type === 'password') return false;
                                return true;
                            }
                        };

                        if (typeof domtoimage !== 'undefined') {
                            dataUrl = await domtoimage.toJpeg(document.body, options);
                        } else if (typeof html2canvas !== 'undefined') {
                            const canvas = await html2canvas(document.body, {
                                useCORS: true,
                                scale: Math.min(window.devicePixelRatio, 2),
                                ignoreElements: (el) => el.dataset.html2canvasIgnore === 'true' || el.classList.contains('bugmask') || el.dataset.bugmask === 'true'
                            });
                            dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                        }

                        widgetEl.style.display = originalDisplay;
                        return dataUrl;
                    } catch (e) {
                        console.error('Screenshot failed', e);
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
                                route: document.documentElement.dataset.routeName || null,
                                referrer: document.referrer,
                                area: this.getSourceArea(),
                                locale: document.documentElement.lang || 'cs',
                                timestamp: new Date().toISOString(),
                                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                                appVersion: document.documentElement.dataset.appVersion || '1.0',
                                requestId: this.getResponseHeader('X-Request-ID'),
                                user: {
                                    id: document.documentElement.dataset.userId || null,
                                    email: document.documentElement.dataset.userEmail || null,
                                    roles: (document.documentElement.dataset.userRoles || '').split(',').filter(Boolean)
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
                                console: this.logs.toArray(),
                                errors: this.errors.toArray(),
                                network: this.networkFailures.toArray(),
                                breadcrumbs: this.breadcrumbs.toArray()
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

                        const result = await response.json();
                        if (response.ok) {
                            this.showStatus(result.message, 'success');
                            this.resetForm();
                            this.closeModal();
                        } else {
                            this.showStatus(result.message || 'Chyba při odesílání.', 'error');
                        }
                    } catch (e) {
                        this.showStatus('Došlo k neočekávané chybě.', 'error');
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
                    // Toto je trik, jak získat header z aktuální stránky, pokud byl nastaven serverem
                    // Funguje to jen pro některé requesty, ale X-Request-ID by tam měl být z navigace
                    try {
                        const perf = performance.getEntriesByType('navigation')[0];
                        // PerformanceNavigationTiming bohužel standardně neukazuje custom headers bez Server-Timing
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
            }
        }
    </script>
</div>
