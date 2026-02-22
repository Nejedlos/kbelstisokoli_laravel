@extends('layouts.auth')

@section('content')
<div x-data="{ showHelp: false }">
    <!-- Help Section (Conditional) -->
    <div x-show="showHelp"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative z-10"
         style="display: none;">

        <div class="text-center mb-10">
            <div class="auth-header-visual mb-8" aria-hidden="true">
                <div class="auth-icon-aura"></div>
                <i class="fa-light fa-circle-info auth-icon-bg animate-icon-drift"></i>
            </div>
            <h1 class="auth-title">{{ __('Taktická příručka') }}</h1>
            <p class="auth-sub">{{ __('Jak nastavit dvoufázové ověření') }}</p>
        </div>

        <div class="glass-card space-y-8 border-t-2 border-primary/50">
            <div class="space-y-6">
                <div class="flex gap-4">
                    <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center shrink-0 border border-primary/20 text-[11px] font-black text-white shadow-[0_0_15px_rgba(225,29,72,0.3)]">1</div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-tight text-white mb-1">{{ __('Stáhněte si aplikaci') }}</h3>
                        <p class="text-[11px] text-white/50 leading-relaxed">{{ __('Nainstalujte si Google Authenticator, Microsoft Authenticator nebo podobnou aplikaci z App Store nebo Google Play.') }}</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center shrink-0 border border-primary/20 text-[11px] font-black text-white shadow-[0_0_15px_rgba(225,29,72,0.3)]">2</div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-tight text-white mb-1">{{ __('Naskenujte QR kód') }}</h3>
                        <p class="text-[11px] text-white/50 leading-relaxed">{{ __('V aplikaci zvolte "Přidat účet" a naskenujte kód, který vidíte na obrazovce.') }}</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center shrink-0 border border-primary/20 text-[11px] font-black text-white shadow-[0_0_15px_rgba(225,29,72,0.3)]">3</div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-tight text-white mb-1">{{ __('Zadejte kód') }}</h3>
                        <p class="text-[11px] text-white/50 leading-relaxed">{{ __('Aplikace začne generovat 6místné kódy. Ten aktuální zadejte do políčka a potvrďte.') }}</p>
                    </div>
                </div>
            </div>

            <button @click="showHelp = false" class="fi-btn fi-color-primary w-full py-4 rounded-2xl text-sm group/btn">
                <span class="relative z-10 flex items-center justify-center gap-3">
                    <i class="fa-light fa-arrow-left"></i>
                    {{ __('Zpět k nastavení') }}
                </span>
            </button>
        </div>
    </div>

    <!-- Main Content (Setup) -->
    <div x-show="!showHelp" x-transition class="relative z-10">
        <!-- Header -->
        <x-auth-header
            :title="__('Taktická porada')"
            :subtitle="__('Bezpečný driblink vyžaduje dvoufázové ověření.')"
            icon="fa-shield-plus"
        />

        <div class="glass-card relative overflow-hidden group">
            <!-- Decorative corner accent -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 blur-3xl -mr-16 -mt-16 group-hover:bg-primary/10 transition-colors duration-700"></div>

            @if(! $user->two_factor_secret)
                {{-- Krok 1: Aktivace --}}
                <div class="text-center space-y-8">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto shadow-[0_0_30px_rgba(225,29,72,0.1)]">
                        <i class="fa-light fa-lock-hashtag text-primary text-3xl icon-glow"></i>
                    </div>
                    <div class="space-y-3">
                        <h2 class="text-xl font-black uppercase tracking-tight text-white italic">{{ __('Neprůstřelná obrana') }}</h2>
                        <p class="text-xs text-white/50 font-medium leading-relaxed">
                            {{ __('Jako člen realizačního týmu máš přístup k taktice celého klubu. Musíme tvůj účet bránit jako koš v poslední vteřině.') }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('two-factor.enable') }}">
                        @csrf
                        <button type="submit" class="fi-btn fi-color-primary w-full py-5 rounded-2xl text-base group/btn">
                            <span class="relative z-10 flex items-center justify-center gap-3">
                                {{ __('Aktivovat obranu (2FA)') }}
                                <i class="fa-light fa-shield-check group-hover/btn:scale-110 transition-transform duration-500"></i>
                            </span>
                        </button>
                    </form>
                </div>
            @elseif(! $user->two_factor_confirmed_at)
                {{-- Krok 2: Konfigurace a potvrzení --}}
                <div class="space-y-8">
                    <div class="text-center space-y-3">
                        <h2 class="text-xl font-black uppercase tracking-tight text-white italic">{{ __('Sehranost s aplikací') }}</h2>
                        <p class="text-xs text-white/50 font-medium leading-relaxed">
                            {{ __('Naskenuj tenhle kód do své aplikace, ať jsme v jednom týmu.') }}
                        </p>
                    </div>

                    <div class="text-center">
                        <button type="button" @click="showHelp = true" class="text-[10px] font-black uppercase tracking-widest text-primary/60 hover:text-primary transition-colors flex items-center justify-center gap-2 mx-auto">
                            <i class="fa-light fa-circle-question"></i> {{ __('Potřebujete pomoci?') }}
                        </button>
                    </div>

                    <div class="flex justify-center">
                        <div class="p-6 bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.5)] border-4 border-primary/20 relative group/qr">
                             <div class="absolute inset-0 bg-primary/5 opacity-0 group-hover/qr:opacity-100 transition-opacity rounded-2xl"></div>
                            {!! $user->twoFactorQrCodeSvg() !!}
                        </div>
                    </div>

                    <div class="bg-white/5 rounded-2xl p-5 border border-white/10 text-center relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-primary/5 blur-2xl -mr-8 -mt-8"></div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-white/40 mb-2">{{ __('Nelze naskenovat? Zadejte klíč:') }}</p>
                        <code class="text-[11px] font-bold text-primary bg-white/5 px-4 py-2 rounded-xl block border border-white/5 tracking-wider select-all">
                            {{ decrypt($user->two_factor_secret) }}
                        </code>
                    </div>

                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-6">
                        @csrf
                        <div class="space-y-3">
                            <label for="code" class="fi-fo-field-label text-center block">{{ __('Zadejte 6místný kód z aplikace') }}</label>
                            <div class="relative group/input">
                                <input id="code" type="text" name="code" inputmode="numeric" autofocus required autocomplete="one-time-code"
                                       placeholder="000 000"
                                       class="fi-input-wrp w-full bg-white/5 border {{ $errors->has('code') ? 'border-rose-500/40 shadow-[0_0_15px_rgba(244,63,94,0.1)]' : 'border-white/10' }} rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white transition-all duration-300 font-black text-white focus:text-slate-900 placeholder-white/20 outline-none tracking-[0.4em] text-center text-3xl py-6">
                            </div>
                            @if($errors->has('code'))
                                <p class="fi-error-message block text-center" style="display: block !important;">{{ $errors->first('code') }}</p>
                            @endif
                        </div>

                        <button type="submit" class="fi-btn fi-color-primary w-full py-5 rounded-2xl text-base group/btn">
                            <span class="relative z-10 flex items-center justify-center gap-3">
                                {{ __('Potvrdit nahrávku') }}
                                <i class="fa-light fa-unlock-keyhole group-hover/btn:scale-110 transition-transform duration-500"></i>
                            </span>
                        </button>
                    </form>
                </div>
            @else
                {{-- Krok 3: Potvrzeno, ukaž recovery kódy --}}
                <div class="text-center space-y-8 animate-fade-in">
                    <div class="w-20 h-20 bg-emerald-500/10 rounded-full flex items-center justify-center mx-auto shadow-[0_0_30px_rgba(16,185,129,0.1)]">
                        <i class="fa-light fa-circle-check text-emerald-500 text-4xl icon-bounce"></i>
                    </div>
                    <div class="space-y-3">
                        <h2 class="text-2xl font-black uppercase tracking-tight text-white italic">{{ __('Zabezpečeno') }}</h2>
                        <p class="text-xs text-white/50 font-medium leading-relaxed">
                            {{ __('Obrana je postavená! Dvoufázové ověření bylo úspěšně aktivováno. Níže jsou tvoje záchranné kódy.') }}
                        </p>
                    </div>

                    <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-3xl p-6 space-y-4">
                        <div class="flex items-center gap-3 mb-2">
                             <div class="h-px flex-1 bg-emerald-500/20"></div>
                             <span class="text-[9px] font-black uppercase tracking-[0.2em] text-emerald-400">{{ __('Důležité: Ulož si tyto kódy') }}</span>
                             <div class="h-px flex-1 bg-emerald-500/20"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($user->recoveryCodes() as $code)
                                <div class="bg-slate-950/50 border border-white/5 px-3 py-2 rounded-xl font-mono text-sm text-white text-center tracking-wider">
                                    {{ $code }}
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-white/40 italic font-medium">{{ __('Kódy použij v případě, že ztratíš přístup k telefonu. Bez nich se do kabiny nedostaneš.') }}</p>
                    </div>

                    <div class="pt-4">
                        <a href="{{ session('url.intended') ?? route('filament.admin.pages.dashboard') }}" class="fi-btn fi-color-primary w-full py-5 rounded-2xl text-base group/btn">
                            <span class="relative z-10 flex items-center justify-center gap-3">
                                {{ __('Vstoupit do kabiny') }}
                                <i class="fa-light fa-arrow-right-long group-hover/btn:translate-x-2 transition-transform duration-500"></i>
                            </span>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-12 text-center animate-fade-in space-y-6" style="animation-delay: 0.4s">
            <div class="flex flex-col gap-4">
                <a href="{{ route('member.dashboard') }}" class="auth-footer-link-primary flex items-center justify-center gap-2">
                    <i class="fa-light fa-arrow-left-long"></i> {{ __('Přejít do členské sekce') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-rose-500/60 hover:text-rose-500 text-xs font-black uppercase tracking-widest transition-colors">
                        {{ __('Odhlásit se') }}
                    </button>
                </form>
            </div>
        </div>

        <x-auth-footer :show-back="false" />
    </div>
</div>
@endsection
