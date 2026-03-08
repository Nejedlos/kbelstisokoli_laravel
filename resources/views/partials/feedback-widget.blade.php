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
                                <input type="checkbox" x-model="options.clicks" class="rounded text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900">Posledních 200 kliků</span>
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
        function ksFeedbackWidget() {
            return {
                isOpen: false,
                submitting: false,
                logs: [],
                networkFailures: [],
                clicks: [],
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
                    clicks: false,
                },
                status: {
                    show: false,
                    message: '',
                    type: 'success'
                },
                debugInfo: '',

                init() {
                    this.setupLogging();
                    this.setupNetworkTracking();
                    this.setupClickTracking();
                    this.debugInfo = `v${document.documentElement.dataset.appVersion || '1.0'}`;

                    // ESC to close
                    window.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && this.isOpen) this.closeModal();
                    });
                },

                openModal() {
                    this.isOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                closeModal() {
                    this.isOpen = false;
                    document.body.style.overflow = '';
                },

                setupLogging() {
                    const originalConsole = {
                        log: console.log,
                        warn: console.warn,
                        error: console.error,
                        info: console.info,
                        debug: console.debug
                    };

                    const pushLog = (type, args) => {
                        try {
                            const entry = {
                                type,
                                timestamp: new Date().toISOString(),
                                data: Array.from(args).map(arg => {
                                    try {
                                        return typeof arg === 'object' ? JSON.parse(JSON.stringify(arg)) : String(arg);
                                    } catch (e) {
                                        return '[unserializable]';
                                    }
                                })
                            };
                            this.logs.push(entry);
                            if (this.logs.length > 300) this.logs.shift();
                        } catch (e) {}
                    };

                    ['log', 'warn', 'error', 'info', 'debug'].forEach(type => {
                        console[type] = (...args) => {
                            pushLog(type, args);
                            originalConsole[type].apply(console, args);
                        };
                    });

                    window.addEventListener('error', (event) => {
                        pushLog('exception', [event.message, event.filename, event.lineno]);
                    });

                    window.addEventListener('unhandledrejection', (event) => {
                        pushLog('promise-rejection', [event.reason]);
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
                                this.networkFailures.push({
                                    method: args[1]?.method || 'GET',
                                    url: args[0],
                                    status: response.status,
                                    duration: Date.now() - start,
                                    timestamp: new Date().toISOString()
                                });
                            }
                            return response;
                        } catch (error) {
                            this.networkFailures.push({
                                method: args[1]?.method || 'GET',
                                url: args[0],
                                status: 'EXCEPTION',
                                error: error.message,
                                duration: Date.now() - start,
                                timestamp: new Date().toISOString()
                            });
                            throw error;
                        }
                    };

                    // XHR wrap
                    const originalXhr = window.XMLHttpRequest.prototype.open;
                    window.XMLHttpRequest.prototype.open = function(method, url, ...args) {
                        this.addEventListener('load', () => {
                            if (this.status >= 400) {
                                window.dispatchEvent(new CustomEvent('ks-network-failure', {
                                    detail: { method, url, status: this.status, timestamp: new Date().toISOString() }
                                }));
                            }
                        });
                        return originalXhr.apply(this, [method, url, ...args]);
                    };

                    window.addEventListener('ks-network-failure', (e) => {
                        this.networkFailures.push(e.detail);
                        if (this.networkFailures.length > 100) this.networkFailures.shift();
                    });
                },

                setupClickTracking() {
                    document.addEventListener('click', (e) => {
                        if (!this.options.clicks) return;

                        const el = e.target;
                        const descriptor = `${el.tagName.toLowerCase()}${el.id ? '#'+el.id : ''}${el.className ? '.'+el.className.split(' ').join('.') : ''}`;

                        this.clicks.push({
                            x: e.clientX,
                            y: e.clientY,
                            element: descriptor.substring(0, 100),
                            text: (el.innerText || el.value || '').substring(0, 30),
                            timestamp: new Date().toISOString()
                        });

                        if (this.clicks.length > 200) this.clicks.shift();
                    }, true);
                },

                async captureScreenshot() {
                    const widgetEl = document.getElementById('ks-feedback-widget');
                    const originalDisplay = widgetEl.style.display;
                    widgetEl.style.display = 'none';

                    try {
                        let dataUrl = '';
                        if (typeof domtoimage !== 'undefined') {
                            dataUrl = await domtoimage.toJpeg(document.body, {
                                quality: 0.8,
                                bgcolor: '#ffffff',
                                filter: (node) => {
                                    if (node.dataset?.html2canvasIgnore === 'true') return false;
                                    if (node.classList?.contains('bugmask')) return false;
                                    return true;
                                }
                            });
                        } else if (typeof html2canvas !== 'undefined') {
                            const canvas = await html2canvas(document.body, {
                                useCORS: true,
                                scale: Math.min(window.devicePixelRatio, 2),
                                ignoreElements: (el) => el.dataset.html2canvasIgnore === 'true' || el.classList.contains('bugmask')
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
                        let screenshot = null;
                        if (this.options.screenshot) {
                            screenshot = await this.captureScreenshot();
                        }

                        const payload = {
                            ...this.form,
                            url: window.location.href,
                            route_name: document.documentElement.dataset.routeName || null,
                            page_title: document.title,
                            user_agent: navigator.userAgent,
                            viewport: { w: window.innerWidth, h: window.innerHeight },
                            screen: { w: window.screen.width, h: window.screen.height, dpr: window.devicePixelRatio },
                            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                            source_area: this.getSourceArea(),
                            screenshot: screenshot,
                            logs: this.options.logs ? this.logs : [],
                            network: this.options.network ? this.networkFailures : [],
                            clicks: this.options.clicks ? this.clicks : []
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
                    this.form = { type: 'bug', severity: 'medium', title: '', description: '', steps: '' };
                }
            }
        }
    </script>
</div>
