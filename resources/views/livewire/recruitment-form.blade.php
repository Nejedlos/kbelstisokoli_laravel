@php
    $siteKey = config('recaptcha.site_key');
    $recaptchaEnabled = (bool) config('recaptcha.enabled');
@endphp

<div class="recruitment-form-wrapper"
    @if($recaptchaEnabled && $siteKey)
    x-data="{
        isSubmitting: false,
        async getRecaptchaToken() {
            console.log('[reCAPTCHA] Vyžaduji token pro akci: recruitment_form');
            return new Promise((resolve) => {
                if (typeof grecaptcha === 'undefined') {
                    console.error('[reCAPTCHA] Chyba: grecaptcha není definováno (není načten script?)');
                    resolve(null);
                    return;
                }
                grecaptcha.ready(() => {
                    console.log('[reCAPTCHA] API připraveno, spouštím execute...');
                    grecaptcha.execute('{{ $siteKey }}', {action: 'recruitment_form'}).then((token) => {
                        if (token) {
                            console.log('[reCAPTCHA] Token úspěšně získán');
                        } else {
                            console.warn('[reCAPTCHA] Token byl prázdný');
                        }
                        resolve(token);
                    });
                });
            });
        },
        async submitForm() {
            console.log('[Form] Odesílám náborový formulář...');
            if (this.isSubmitting) return;
            this.isSubmitting = true;
            const token = await this.getRecaptchaToken();
            if (token) {
                console.log('[Form] Token reCAPTCHA získán, odesílám data na server...');
                @this.set('recaptchaToken', token);
                @this.submit();
            } else {
                console.error('[Form] Nepodařilo se získat reCAPTCHA token.');
                this.isSubmitting = false;
                alert('Chyba reCAPTCHA. Zkontrolujte prosím připojení nebo nastavení.');
            }
        }
    }"
    x-on:livewire:initialized.window="console.log('[Form] Náborový formulář inicializován'); isSubmitting = false"
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
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                    @foreach($teams as $slug => $label)
                        <label class="relative cursor-pointer">
                            <input type="radio" wire:model.live="selectedTeam" value="{{ $slug }}" @if($selectedTeam === $slug) checked @endif class="peer sr-only">
                            <div class="p-3 rounded-2xl border-2 border-slate-100 bg-slate-50 peer-checked:border-primary peer-checked:bg-primary/5 transition-all text-center group">
                                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center mx-auto mb-2 shadow-sm text-slate-400 group-hover:text-primary transition-colors peer-checked:text-primary">
                                    <i class="fa-light fa-basketball"></i>
                                </div>
                                <span class="block text-[10px] font-black uppercase tracking-tighter text-slate-600 peer-checked:text-primary leading-tight line-clamp-2 min-h-[2.5em] flex items-center justify-center">{{ $label }}</span>
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
                        <input wire:model.blur="name" type="text" id="name" class="w-full bg-slate-50 border-slate-200 rounded-2xl pl-11 py-3 text-sm focus:border-primary focus:ring-primary/20 transition-all @error('name') border-rose-500 ring-rose-200 @enderror" placeholder="Jan Novák" required>
                    </div>
                    @error('name') <span class="text-xs text-rose-600 font-bold tracking-tight ml-1">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label for="email" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Váš e-mail</label>
                    <div class="relative group">
                        <i class="fa-light fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"></i>
                        <input wire:model.blur="email" type="email" id="email" class="w-full bg-slate-50 border-slate-200 rounded-2xl pl-11 py-3 text-sm focus:border-primary focus:ring-primary/20 transition-all @error('email') border-rose-500 ring-rose-200 @enderror" placeholder="email@priklad.cz" required>
                    </div>
                    @error('email') <span class="text-xs text-rose-600 font-bold tracking-tight ml-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="bg-slate-50/50 p-6 rounded-[2rem] border border-slate-100 space-y-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                        <i class="fa-light fa-basketball"></i>
                    </div>
                    <h4 class="text-sm font-black uppercase tracking-widest text-slate-700">Basketbalový dotazník</h4>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="age" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Věk</label>
                        <div class="relative group">
                            <i class="fa-light fa-calendar absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"></i>
                            <input wire:model.blur="age" type="number" id="age" class="w-full bg-white border-slate-200 rounded-2xl pl-11 py-3 text-sm focus:border-primary focus:ring-primary/20 transition-all @error('age') border-rose-500 ring-rose-200 @enderror" placeholder="Např. 25">
                        </div>
                        @error('age') <span class="text-xs text-rose-600 font-bold tracking-tight ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="height" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Výška (cm)</label>
                        <div class="relative group">
                            <i class="fa-light fa-ruler-vertical absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"></i>
                            <input wire:model.blur="height" type="number" id="height" class="w-full bg-white border-slate-200 rounded-2xl pl-11 py-3 text-sm focus:border-primary focus:ring-primary/20 transition-all @error('height') border-rose-500 ring-rose-200 @enderror" placeholder="Např. 195">
                        </div>
                        @error('height') <span class="text-xs text-rose-600 font-bold tracking-tight ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="position" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Preferovaná pozice</label>
                        <div class="relative group">
                            <i class="fa-light fa-users-viewfinder absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"></i>
                            <select wire:model.blur="position" id="position" class="w-full bg-white border-slate-200 rounded-2xl pl-11 py-3 text-sm focus:border-primary focus:ring-primary/20 transition-all appearance-none @error('position') border-rose-500 ring-rose-200 @enderror">
                                <option value="">Vyberte pozici...</option>
                                <option value="PG">Rozehrávač (PG)</option>
                                <option value="SG">Křídlo / Rozehrávač (SG)</option>
                                <option value="SF">Křídlo (SF)</option>
                                <option value="PF">Pivot / Křídlo (PF)</option>
                                <option value="C">Pivot (C)</option>
                                <option value="all-around">Všestranný hráč</option>
                            </select>
                            <i class="fa-light fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                        </div>
                        @error('position') <span class="text-xs text-rose-600 font-bold tracking-tight ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="level" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Nejvyšší hraná soutěž</label>
                        <div class="relative group">
                            <i class="fa-light fa-trophy absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"></i>
                            <input wire:model.blur="level" type="text" id="level" class="w-full bg-white border-slate-200 rounded-2xl pl-11 py-3 text-sm focus:border-primary focus:ring-primary/20 transition-all @error('level') border-rose-500 ring-rose-200 @enderror" placeholder="Např. 2. liga, Přebor...">
                        </div>
                        @error('level') <span class="text-xs text-rose-600 font-bold tracking-tight ml-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label for="message" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Zpráva pro trenéra</label>
                <textarea wire:model.blur="message" id="message" rows="5" class="w-full bg-slate-50 border-slate-200 rounded-2xl p-4 text-sm focus:border-primary focus:ring-primary/20 transition-all @error('message') border-rose-500 ring-rose-200 @enderror" placeholder="Představte se nám krátce..." required></textarea>
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
                <div class="flex items-center gap-4 bg-emerald-50/50 p-5 rounded-[1.5rem] border border-emerald-100/50 group/captcha transition-all hover:bg-emerald-50 hover:border-emerald-200">
                    <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-emerald-500 shadow-sm transition-transform group-hover/captcha:scale-110">
                        <i class="fa-light fa-shield-check fa-lg"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-800 mb-0.5 leading-none">Antispamová ochrana aktivní</p>
                        <p class="text-[10px] text-emerald-600/80 leading-tight">
                            Tento web je chráněn pomocí reCAPTCHA. Platí <a href="https://policies.google.com/privacy" class="font-bold underline hover:text-primary">Soukromí</a> a <a href="https://policies.google.com/terms" class="font-bold underline hover:text-primary">Podmínky</a> Google.
                        </p>
                    </div>
                    <div class="hidden sm:block opacity-20 group-hover/captcha:opacity-40 transition-opacity">
                        <i class="fa-brands fa-google fa-2x"></i>
                    </div>
                </div>
            @endif
        </form>
    @endif
</div>

@if($recaptchaEnabled && $siteKey)
    @push('scripts')
        <script src="https://www.google.com/recaptcha/api.js?render={{ $siteKey }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('[reCAPTCHA] Ochrana náborového formuláře aktivní (v3)');
            });
        </script>
        <style>
            .grecaptcha-badge { visibility: visible !important; }
        </style>
    @endpush
@endif
