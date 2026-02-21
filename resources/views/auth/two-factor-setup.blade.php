@extends('layouts.public')

@section('content')
<div class="auth-gradient">
    <!-- Floating Background Objects -->
    <div class="floating-objects">
        <div class="floating-ball w-64 h-64 top-[-10%] right-[-5%] bg-primary opacity-10"></div>
        <div class="floating-ball w-96 h-96 bottom-[-15%] left-[-10%] opacity-5"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <!-- Header -->
        <div class="text-center mb-12 animate-fade-in-down">
            @if($branding['logo_path'] ?? null)
                <div class="w-24 h-24 bg-white/5 backdrop-blur-md rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl border border-white/10 p-4 transition-transform hover:scale-105 duration-500">
                    <img src="{{ asset('storage/' . $branding['logo_path']) }}" class="max-w-full max-h-full object-contain filter drop-shadow-lg" alt="{{ $branding['club_name'] }}">
                </div>
            @else
                <div class="w-20 h-20 mx-auto mb-8 text-primary flex items-center justify-center">
                    <i class="fa-duotone fa-light fa-shield-plus text-6xl icon-bounce icon-glow"></i>
                </div>
            @endif
            <h1 class="auth-title">Zabezpečení účtu</h1>
            <p class="auth-sub tracking-tight">Pro přístup do administrace je vyžadováno 2FA</p>
        </div>

        <div class="glass-card p-10 border-t-2 border-primary/50 relative overflow-hidden group">
            @if(! $user->two_factor_secret)
                {{-- Krok 1: Aktivace --}}
                <div class="text-center space-y-8">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto shadow-[0_0_30px_rgba(225,29,72,0.1)]">
                        <i class="fa-light fa-lock-hashtag text-primary text-3xl icon-glow"></i>
                    </div>
                    <div class="space-y-3">
                        <h2 class="text-xl font-black uppercase tracking-tight text-white italic">Maximální bezpečnost</h2>
                        <p class="text-xs text-slate-400 font-medium leading-relaxed">
                            Jako správce týmu máte přístup k citlivým datům a interní strategii. **Dvoufázové ověření (2FA)** je povinné k ochraně vašeho účtu i celého klubu.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('two-factor.enable') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full py-5 rounded-2xl text-base btn-glow group/btn">
                            <span class="relative z-10 flex items-center justify-center gap-3">
                                Povolit dvoufázové ověření
                                <i class="fa-light fa-shield-check group-hover/btn:scale-110 transition-transform duration-500"></i>
                            </span>
                        </button>
                    </form>
                </div>
            @elseif(! $user->two_factor_confirmed_at)
                {{-- Krok 2: Konfigurace a potvrzení --}}
                <div class="space-y-8">
                    <div class="text-center space-y-3">
                        <h2 class="text-xl font-black uppercase tracking-tight text-white italic">Propojení aplikace</h2>
                        <p class="text-xs text-slate-400 font-medium leading-relaxed">
                            Naskenujte tento kód ve vaší autentizační aplikaci (Google Authenticator, Microsoft Authenticator, Authy apod.).
                        </p>
                    </div>

                    <div class="flex justify-center">
                        <div class="p-6 bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.5)] border-4 border-primary/20 relative group/qr">
                             <div class="absolute inset-0 bg-primary/5 opacity-0 group-hover/qr:opacity-100 transition-opacity rounded-2xl"></div>
                            {!! $user->twoFactorQrCodeSvg() !!}
                        </div>
                    </div>

                    <div class="bg-white/5 rounded-2xl p-5 border border-white/10 text-center relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-primary/5 blur-2xl -mr-8 -mt-8"></div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Nelze naskenovat? Zadejte klíč:</p>
                        <code class="text-[11px] font-bold text-primary bg-white/5 px-4 py-2 rounded-xl block border border-white/5 tracking-wider select-all">
                            {{ decrypt($user->two_factor_secret) }}
                        </code>
                    </div>

                    <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-6">
                        @csrf
                        <div class="space-y-3">
                            <label for="code" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-center block">Zadejte 6místný kód z aplikace</label>
                            <div class="relative group/input">
                                <div class="input-icon group-focus-within/input:text-primary">
                                    <i class="fa-light fa-fingerprint text-xl"></i>
                                </div>
                                <input id="code" type="text" name="code" inputmode="numeric" autofocus required autocomplete="one-time-code"
                                       placeholder="000 000"
                                       class="w-full input-with-icon bg-white/5 border {{ $errors->has('code') ? 'border-rose-500/40 shadow-[0_0_15px_rgba(244,63,94,0.1)]' : 'border-white/10' }} rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white/10 transition-all duration-300 font-black text-white placeholder-slate-700 outline-none tracking-[0.4em] text-center text-3xl py-6">
                            </div>
                            @error('code')
                                <div class="flex items-center justify-center gap-2 text-rose-400 mt-2 animate-shake">
                                    <i class="fa-light fa-circle-exclamation text-[10px]"></i>
                                    <p class="text-[10px] font-bold tracking-wide">{{ $message }}</p>
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-full py-5 rounded-2xl text-base btn-glow group/btn">
                            <span class="relative z-10 flex items-center justify-center gap-3">
                                Ověřit a aktivovat
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
                        <h2 class="text-2xl font-black uppercase tracking-tight text-white italic">Zabezpečeno</h2>
                        <p class="text-xs text-slate-400 font-medium leading-relaxed">
                            Dvoufázové ověření bylo úspěšně aktivováno. Níže jsou vaše **záchranné kódy**.
                        </p>
                    </div>

                    <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-3xl p-6 space-y-4">
                        <div class="flex items-center gap-3 mb-2">
                             <div class="h-px flex-1 bg-emerald-500/20"></div>
                             <span class="text-[9px] font-black uppercase tracking-[0.2em] text-emerald-400">Důležité: Uložte si tyto kódy</span>
                             <div class="h-px flex-1 bg-emerald-500/20"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            @foreach ($user->recoveryCodes() as $code)
                                <div class="bg-slate-950/50 border border-white/5 px-3 py-2 rounded-xl font-mono text-sm text-white text-center tracking-wider">
                                    {{ $code }}
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-slate-500 italic font-medium">Kódy použijte v případě, že ztratíte přístup k aplikaci v telefonu.</p>
                    </div>

                    <div class="pt-4">
                        <a href="{{ session('url.intended') ?? route('filament.admin.pages.dashboard') }}" class="btn btn-primary w-full py-5 rounded-2xl text-base btn-glow group/btn">
                            <span class="relative z-10 flex items-center justify-center gap-3">
                                Vstoupit do administrace
                                <i class="fa-light fa-arrow-right-long group-hover/btn:translate-x-2 transition-transform duration-500"></i>
                            </span>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-12 text-center animate-fade-in space-y-4" style="animation-delay: 0.4s">
            <a href="{{ route('member.dashboard') }}" class="block text-[10px] font-black uppercase tracking-widest text-primary hover:text-primary-light transition-colors">
                <i class="fa-light fa-arrow-left-long mr-2"></i> Přejít do členské sekce (bez admin přístupu)
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-rose-400 transition-colors">
                    Odhlásit se
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
