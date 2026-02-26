@php
    $recaptchaService = app(App\Services\RecaptchaService::class);
    $siteKey = $recaptchaService->getSiteKey();
    $recaptchaEnabled = $recaptchaService->isEnabled();
@endphp

<div class="recruitment-form-wrapper"
    @if($recaptchaEnabled && $siteKey)
    x-data="{
        isSubmitting: false,
        async getRecaptchaToken() {
            return new Promise((resolve) => {
                if (typeof grecaptcha === 'undefined') {
                    console.error('reCAPTCHA not loaded');
                    resolve(null);
                    return;
                }
                grecaptcha.ready(() => {
                    grecaptcha.execute('{{ $siteKey }}', {action: 'recruitment_form'}).then((token) => {
                        resolve(token);
                    });
                });
            });
        },
        async submitForm() {
            if (this.isSubmitting) return;
            this.isSubmitting = true;
            const token = await this.getRecaptchaToken();
            if (token) {
                @this.set('recaptchaToken', token);
                @this.submit();
            } else {
                this.isSubmitting = false;
                alert('Chyba reCAPTCHA. Zkontrolujte prosím připojení nebo nastavení.');
            }
        }
    }"
    x-on:livewire:initialized.window="isSubmitting = false"
    @else
    x-data="{ isSubmitting: false }"
    @endif
>
    @if($success)
        <div class="bg-emerald-50 border border-emerald-100 p-8 rounded-[2rem] text-center animate-fade-in" role="alert">
            <div class="w-20 h-20 bg-emerald-500 text-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/20">
                <i class="fa-light fa-paper-plane-check fa-3xl"></i>
            </div>
            <h3 class="text-2xl font-black text-emerald-900 uppercase tracking-tighter mb-2">Žádost odeslána!</h3>
            <p class="text-emerald-700/80 mb-8 leading-relaxed">
                Děkujeme za váš zájem. Vaše žádost byla doručena trenérovi týmu. Brzy se vám ozveme zpět.
            </p>
            <button type="button" wire:click="$set('success', false)" class="btn btn-primary px-8">
                Poslat další žádost
            </button>
        </div>
    @else
        <form
            @if($recaptchaEnabled && $siteKey)
                x-on:submit.prevent="submitForm"
            @else
                wire:submit.prevent="submit"
                x-on:submit="isSubmitting = true"
            @endif
            class="space-y-6"
        >
            @if($errorMessage)
                <div class="bg-rose-50 border border-rose-100 text-rose-700 px-6 py-4 rounded-2xl flex items-center gap-4 animate-shake" role="alert">
                    <i class="fa-light fa-circle-exclamation text-rose-500 text-xl"></i>
                    <span class="text-sm font-bold tracking-tight">{{ $errorMessage }}</span>
                </div>
            @endif

            <div class="space-y-4">
                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Vyberte tým</label>
                <div class="grid grid-cols-2 gap-4">
                    @foreach($teams as $slug => $label)
                        <label class="relative cursor-pointer">
                            <input type="radio" wire:model.live="selectedTeam" value="{{ $slug }}" class="peer sr-only">
                            <div class="p-4 rounded-2xl border-2 border-slate-100 bg-slate-50 peer-checked:border-primary peer-checked:bg-primary/5 transition-all text-center group">
                                <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center mx-auto mb-2 shadow-sm text-slate-400 group-hover:text-primary transition-colors peer-checked:text-primary">
                                    <i class="fa-light fa-basketball fa-lg"></i>
                                </div>
                                <span class="block text-sm font-black uppercase tracking-tighter text-slate-600 peer-checked:text-primary">{{ $label }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('selectedTeam') <span class="text-xs text-rose-600 font-bold tracking-tight ml-1">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="name" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Vaše jméno</label>
                    <div class="relative group">
                        <i class="fa-light fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"></i>
                        <input wire:model="name" type="text" id="name" class="w-full bg-slate-50 border-slate-200 rounded-2xl pl-11 py-3 text-sm focus:border-primary focus:ring-primary/20 transition-all" placeholder="Jan Novák" required>
                    </div>
                    @error('name') <span class="text-xs text-rose-600 font-bold tracking-tight ml-1">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label for="email" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Váš e-mail</label>
                    <div class="relative group">
                        <i class="fa-light fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"></i>
                        <input wire:model="email" type="email" id="email" class="w-full bg-slate-50 border-slate-200 rounded-2xl pl-11 py-3 text-sm focus:border-primary focus:ring-primary/20 transition-all" placeholder="email@priklad.cz" required>
                    </div>
                    @error('email') <span class="text-xs text-rose-600 font-bold tracking-tight ml-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="space-y-2">
                <label for="message" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Zpráva pro trenéra</label>
                <textarea wire:model="message" id="message" rows="5" class="w-full bg-slate-50 border-slate-200 rounded-2xl p-4 text-sm focus:border-primary focus:ring-primary/20 transition-all" placeholder="Představte se nám krátce..." required></textarea>
                @error('message') <span class="text-xs text-rose-600 font-bold tracking-tight ml-1">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4">
                <button
                    type="submit"
                    x-bind:disabled="isSubmitting"
                    class="btn btn-primary btn-lg w-full relative group disabled:opacity-70 disabled:cursor-not-allowed overflow-hidden"
                >
                    <span x-show="!isSubmitting" class="flex items-center gap-3">
                        Odeslat žádost o nábor
                        <i class="fa-light fa-arrow-right text-sm transition-transform group-hover:translate-x-1"></i>
                    </span>
                    <span x-show="isSubmitting" x-cloak class="flex items-center gap-3">
                        <i class="fa-light fa-spinner-third animate-spin"></i>
                        Odesílám...
                    </span>
                </button>
            </div>

            @if($recaptchaEnabled && $siteKey)
                <div class="flex items-center gap-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <i class="fa-light fa-shield-halved text-emerald-500"></i>
                    <p class="text-[10px] text-slate-400 leading-tight">
                        Tento web je chráněn pomocí reCAPTCHA a platí <a href="https://policies.google.com/privacy" class="underline hover:text-primary transition-colors">Zásady ochrany osobních údajů</a> a <a href="https://policies.google.com/terms" class="underline hover:text-primary transition-colors">Smluvní podmínky</a> společnosti Google.
                    </p>
                </div>
            @endif
        </form>
    @endif
</div>

@if($recaptchaEnabled && $siteKey)
    @push('scripts')
        <script src="https://www.google.com/recaptcha/api.js?render={{ $siteKey }}"></script>
    @endpush
@endif
