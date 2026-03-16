    <div id="ks-fb-root"
         x-data="ksFeedbackWidget"
         x-init="init()"
         @ks-feedback-open.window="openModal()"
         class="ks-feedback-system"
     data-html2canvas-ignore="true"
     data-user-id="{{ Auth::id() }}"
     data-user-email="{{ Auth::user()?->email }}"
     data-user-roles="{{ Auth::user()?->getRoleNames()?->implode(',') }}"
     data-app-version="{{ config('app.version', '1.0') }}"
     data-route-name="{{ Route::currentRouteName() }}">

    <!-- Floating Action Button -->
    <button @click="$dispatch('ks-feedback-open')"
            type="button"
            class="ks-fab-trigger fixed left-4 bottom-[calc(env(safe-area-inset-bottom,0)+16px)] md:left-6 md:bottom-6 w-14 h-14 md:w-12 md:h-12 bg-rose-600 hover:bg-rose-700 text-white rounded-full shadow-2xl transition-all duration-300 flex items-center justify-center group overflow-hidden"
            style="z-index: 2147483647 !important; background-color: #e11d48 !important; display: flex !important; pointer-events: auto !important; visibility: visible !important; opacity: 1 !important;"
            :class="isOpen ? 'scale-0 opacity-0' : 'scale-100 opacity-100'"
            aria-label="Odeslat zpětnou vazbu">
        <i class="fa-light fa-bug text-2xl group-hover:rotate-12 transition-transform" style="opacity: 1 !important; visibility: visible !important;"></i>
        <span class="max-w-0 overflow-hidden group-hover:max-w-xs group-hover:ml-2 transition-all duration-500 ease-in-out whitespace-nowrap text-sm font-medium">
            Feedback
        </span>
    </button>

    <!-- Overlay -->
    <div x-show="isOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeModal()"
         class="ks-fb-overlay fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[10000] flex items-center justify-center p-4">

        <!-- Modal -->
        <div x-show="isOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             @click.stop
             class="ks-fb-modal bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col border border-slate-200">

            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center">
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
                <form id="ks-fb-form" @submit.prevent="submitFeedback()">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- Type -->
                        <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-slate-700">Typ hlášení <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="relative flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all"
                                       :class="form.type === 'bug' ? 'border-rose-500 bg-rose-50 text-rose-700' : 'border-slate-100 bg-slate-50 text-slate-500 hover:bg-slate-100'">
                                    <input type="radio" x-model="form.type" value="bug" class="sr-only">
                                    <i class="fa-light fa-bug mb-1"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Bug</span>
                                </label>
                                <label class="relative flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all"
                                       :class="form.type === 'idea' ? 'border-rose-500 bg-rose-50 text-rose-700' : 'border-slate-100 bg-slate-50 text-slate-500 hover:bg-slate-100'">
                                    <input type="radio" x-model="form.type" value="idea" class="sr-only">
                                    <i class="fa-light fa-lightbulb mb-1"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Nápad</span>
                                </label>
                                <label class="relative flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all"
                                       :class="form.type === 'feedback' ? 'border-rose-500 bg-rose-50 text-rose-700' : 'border-slate-100 bg-slate-50 text-slate-500 hover:bg-slate-100'">
                                    <input type="radio" x-model="form.type" value="feedback" class="sr-only">
                                    <i class="fa-light fa-message-smile mb-1"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Ostatní</span>
                                </label>
                            </div>
                        </div>

                        <!-- Severity -->
                        <div class="space-y-1.5">
                            <label class="text-sm font-semibold text-slate-700">Závažnost</label>
                            <select x-model="form.severity" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-rose-500 focus:border-rose-500 bg-white shadow-sm transition-all">
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
                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-rose-500 focus:border-rose-500 bg-white shadow-sm transition-all">
                    </div>

                    <!-- Description -->
                    <div class="space-y-1.5 mb-4">
                        <label class="text-sm font-semibold text-slate-700">Popis <span class="text-red-500">*</span></label>
                        <textarea x-model="form.description" required rows="4" maxlength="5000" placeholder="Detailnější popis..."
                                  class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-rose-500 focus:border-rose-500 bg-white shadow-sm transition-all"></textarea>
                    </div>

                    <!-- Steps (Conditional for bugs) -->
                    <div x-show="form.type === 'bug'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="space-y-1.5 mb-4">
                        <label class="text-sm font-semibold text-slate-700">Kroky k reprodukci</label>
                        <textarea x-model="form.steps" rows="3" maxlength="10000" placeholder="1. Klikněte na... 2. Pak udělejte..."
                                  class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-rose-500 focus:border-rose-500 bg-white shadow-sm transition-all"></textarea>
                    </div>

                    <!-- Options -->
                    <div class="bg-slate-50 rounded-2xl p-4 mb-4">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Přiložit k hlášení</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" x-model="options.screenshot" class="rounded text-rose-600 focus:ring-rose-500">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900">Aktuální screenshot</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" x-model="options.logs" class="rounded text-rose-600 focus:ring-rose-500">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900">Logy konzole (<span x-text="logs ? logs.length : 0"></span>)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" x-model="options.network" class="rounded text-rose-600 focus:ring-rose-500">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900">Chyby sítě (<span x-text="networkFailures ? networkFailures.length : 0"></span>)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" x-model="options.performance" class="rounded text-rose-600 focus:ring-rose-500">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900">Výkonnostní data</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" x-model="options.clicks" class="rounded text-rose-600 focus:ring-rose-500">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900">Kliky (aktivovat)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" x-model="options.dom" class="rounded text-rose-600 focus:ring-rose-500">
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
                            class="px-6 py-2 bg-rose-600 hover:bg-rose-700 disabled:bg-slate-300 text-white text-sm font-bold rounded-xl shadow-lg shadow-rose-500/30 transition-all flex items-center gap-2">
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
        .ks-fab-trigger {
            left: 24px !important;
            bottom: 24px !important;
            width: 48px !important;
            height: 48px !important;
            z-index: 2147483647 !important;
            background-color: #e11d48 !important;
            display: flex !important;
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
        }
        @media (max-width: 640px) {
            .ks-fab-trigger {
                left: 16px !important;
                bottom: calc(env(safe-area-inset-bottom, 0px) + 16px) !important;
                width: 56px !important;
                height: 56px !important;
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

    <!-- Dependencies (Bundled in feedback-widget.js) -->
</div>
