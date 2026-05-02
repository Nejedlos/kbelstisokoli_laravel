@props(['branding' => []])

<div x-data="cookieConsent()"
     x-init="init()"
     x-show="show"
     @open-cookie-settings.window="show = true; document.body.classList.add('overflow-hidden')"
     x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100">

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-secondary/80 backdrop-blur-sm"></div>

    {{-- Modal --}}
    <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-200"
         @click.away="/* Bráníme zavření kliknutím vedle - musí se rozhodnout */">

        {{-- Basketball branding top bar --}}
        <div class="h-2 bg-gradient-to-r from-primary via-primary-400 to-primary"></div>

        <div class="p-6 sm:p-10">
            <div class="flex flex-col sm:flex-row items-center gap-6 mb-8">
                <div class="flex-shrink-0 w-20 h-20 bg-primary-50 rounded-full flex items-center justify-center text-primary relative">
                    <i class="fa-light fa-basketball fa-4x animate-bounce-slow"></i>
                    <div class="absolute -bottom-1 -right-1 bg-white rounded-full p-1 shadow-sm">
                         <i class="fa-solid fa-cookie-bite text-amber-600 fa-lg"></i>
                    </div>
                </div>
                <div class="text-center sm:text-left">
                    <h2 class="text-2xl sm:text-3xl font-display font-bold text-secondary mb-2 leading-tight">
                        {{ __('cookies.title') }}
                    </h2>
                    <p class="text-slate-600 leading-relaxed">
                        {{ __('cookies.description') }}
                    </p>
                </div>
            </div>

            <div class="space-y-4 mb-10">
                {{-- Necessary --}}
                <div class="flex items-start gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    <div class="mt-1">
                        <i class="fa-light fa-shield-check text-success-600 fa-xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-secondary">{{ __('cookies.necessary') }}</span>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 bg-slate-200 px-2 py-0.5 rounded">{{ __('cookies.necessary') }}</span>
                        </div>
                        <p class="text-sm text-slate-500">{{ __('cookies.necessary_desc') }}</p>
                    </div>
                </div>

                {{-- Analytics --}}
                <div class="flex items-start gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100 transition-colors"
                     :class="analytics ? 'bg-primary-50/50 border-primary-100' : ''">
                    <div class="mt-1">
                        <i class="fa-light fa-chart-line-up text-primary fa-xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <label for="cookie-analytics" class="font-bold text-secondary cursor-pointer">{{ __('cookies.analytics') }}</label>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="cookie-analytics" class="sr-only peer" x-model="analytics">
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </div>
                        </div>
                        <p class="text-sm text-slate-500">{{ __('cookies.analytics_desc') }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-6 border-t border-slate-100 pt-8">
                <a href="{{ route('public.gdpr') }}" class="text-sm font-medium text-slate-400 hover:text-primary transition-colors flex items-center gap-2 group">
                    <i class="fa-light fa-circle-info group-hover:rotate-12 transition-transform"></i>
                    {{ __('cookies.link') }}
                </a>
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <button @click="acceptEssential" class="px-6 py-3 rounded-xl font-bold text-slate-500 hover:text-secondary hover:bg-slate-100 transition-all text-sm">
                        {{ __('cookies.accept_essential') }}
                    </button>
                    <button @click="acceptAll" class="px-8 py-4 bg-secondary text-white rounded-xl font-bold hover:bg-secondary-light transition-all transform hover:scale-[1.02] active:scale-[0.98] shadow-xl shadow-secondary/20 flex items-center justify-center gap-3">
                        <i class="fa-light fa-basketball"></i>
                        {{ __('cookies.accept_all') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cookieConsent() {
    return {
        show: false,
        analytics: true,
        cookieName: 'ks_cookie_consent',

        init() {
            const consent = this.getCookie(this.cookieName);
            if (!consent) {
                // Malá pauza před zobrazením pro lepší efekt
                setTimeout(() => {
                    this.show = true;
                    // Zamkneme scroll
                    document.body.classList.add('overflow-hidden');
                }, 1500);
            } else {
                this.applyConsent(JSON.parse(consent));
            }
        },

        acceptAll() {
            this.analytics = true;
            this.saveConsent({
                analytics: true,
                essential: true,
                timestamp: new Date().getTime()
            });
        },

        acceptEssential() {
            this.analytics = false;
            this.saveConsent({
                analytics: false,
                essential: true,
                timestamp: new Date().getTime()
            });
        },

        saveConsent(consent) {
            this.setCookie(this.cookieName, JSON.stringify(consent), 365);
            this.applyConsent(consent);
            this.show = false;
            // Odemkneme scroll
            document.body.classList.remove('overflow-hidden');
        },

        applyConsent(consent) {
            window.ksCookieConsent = consent;

            // Nastavení GTAG consent mode
            if (window.gtag) {
                gtag('consent', 'update', {
                    'analytics_storage': consent.analytics ? 'granted' : 'denied'
                });
            }

            // Pokud máme souhlas s analytikou a GTAG ještě neběží, můžeme ho inicializovat
            // Ale v našem setupu v public.blade.php budeme mít default 'denied' a tady ho jen updatujeme.

            window.dispatchEvent(new CustomEvent('cookie-consent-updated', { detail: consent }));
        },

        setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
        },

        getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
    }
}
</script>

<style>
    @keyframes bounce-slow {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-12px); }
    }
    .animate-bounce-slow {
        animation: bounce-slow 4s infinite ease-in-out;
    }
</style>
