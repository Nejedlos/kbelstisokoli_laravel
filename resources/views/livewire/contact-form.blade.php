@php
    $recaptchaService = app(App\Services\RecaptchaService::class);
    $siteKey = $recaptchaService->getSiteKey();
    $recaptchaEnabled = $recaptchaService->isEnabled();
@endphp

<div class="contact-form-wrapper"
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
                    grecaptcha.execute('{{ $siteKey }}', {action: 'contact_form'}).then((token) => {
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
                <i class="fa-light fa-check fa-3xl"></i>
            </div>
            <h3 class="text-2xl font-black text-emerald-900 uppercase tracking-tighter mb-2">Zpráva odeslána!</h3>
            <p class="text-emerald-700/80 mb-8 leading-relaxed">
                Vaše zpráva byla úspěšně doručena. Brzy se vám ozveme zpět na váš e-mail.
            </p>
            <button type="button" wire:click="$set('success', false)" class="btn btn-primary px-8">
                Poslat další zprávu
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

            @if($toEmail)
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-primary shadow-sm">
                        <i class="fa-light fa-user-tie"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 leading-none mb-1">Příjemce</p>
                        <p class="text-sm font-bold text-slate-700 leading-none">{{ $toEmail }}</p>
                    </div>
                </div>
            @endif

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
                <label for="subject" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Předmět zprávy</label>
                <div class="relative group">
                    <i class="fa-light fa-pen-field absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors"></i>
                    <input wire:model="subject" type="text" id="subject" class="w-full bg-slate-50 border-slate-200 rounded-2xl pl-11 py-3 text-sm focus:border-primary focus:ring-primary/20 transition-all" placeholder="Jaký je předmět vaší zprávy?" required>
                </div>
                @error('subject') <span class="text-xs text-rose-600 font-bold tracking-tight ml-1">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-2">
                <label for="message" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Vaše zpráva</label>
                <textarea wire:model="message" id="message" rows="5" class="w-full bg-slate-50 border-slate-200 rounded-2xl p-4 text-sm focus:border-primary focus:ring-primary/20 transition-all" placeholder="Zde napište, co máte na srdci..." required></textarea>
                @error('message') <span class="text-xs text-rose-600 font-bold tracking-tight ml-1">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-2">
                <label for="attachment" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Příloha (max. 10MB)</label>
                <div class="flex items-center justify-center w-full">
                    <label for="attachment" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-200 border-dashed rounded-2xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-all">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <i class="fa-light fa-cloud-arrow-up text-3xl text-slate-400 mb-2"></i>
                            <p class="mb-1 text-sm text-slate-500 font-bold">Klikněte pro výběr souboru</p>
                            <p class="text-xs text-slate-400 uppercase tracking-widest">PDF, JPG, PNG (max. 10MB)</p>
                        </div>
                        <input wire:model="attachment" type="file" id="attachment" class="hidden">
                    </label>
                </div>
                <div wire:loading wire:target="attachment" class="flex items-center gap-2 text-xs text-primary font-bold animate-pulse mt-2 ml-1">
                    <i class="fa-light fa-spinner-third animate-spin"></i>
                    Nahrávám soubor...
                </div>
                @if ($attachment)
                    <div class="mt-2 p-3 bg-primary/5 border border-primary/10 rounded-xl flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="fa-light fa-file-check text-primary text-xl"></i>
                            <span class="text-sm font-bold text-slate-700">{{ $attachment->getClientOriginalName() }}</span>
                        </div>
                        <button type="button" wire:click="$set('attachment', null)" class="text-slate-400 hover:text-rose-500 transition-colors">
                            <i class="fa-light fa-circle-xmark text-lg"></i>
                        </button>
                    </div>
                @endif
                @error('attachment') <span class="text-xs text-rose-600 font-bold tracking-tight ml-1">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4">
                <button
                    type="submit"
                    x-bind:disabled="isSubmitting"
                    class="btn btn-primary btn-lg w-full relative group disabled:opacity-70 disabled:cursor-not-allowed overflow-hidden"
                >
                    <span x-show="!isSubmitting" class="flex items-center gap-3">
                        Odeslat zprávu
                        <i class="fa-light fa-paper-plane text-sm transition-transform group-hover:translate-x-1 group-hover:-translate-y-1"></i>
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
