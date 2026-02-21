@extends('layouts.public')

@section('content')
<div class="auth-gradient" x-data="{ recovery: false }">
    <!-- Floating Background Objects -->
    <div class="floating-objects">
        <div class="floating-ball w-64 h-64 top-[-10%] right-[-5%] bg-accent"></div>
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
                    <i class="fa-duotone fa-light fa-shield-check text-6xl icon-bounce icon-glow"></i>
                </div>
            @endif
            <h1 class="auth-title">Ověření přístupu</h1>
            <p class="auth-sub tracking-tight" x-show="!recovery">Zadejte kód z vaší aplikace</p>
            <p class="auth-sub tracking-tight" x-show="recovery">Zadejte záchranný kód</p>
        </div>


        <div class="glass-card p-10 border-t-2 border-primary/50 relative overflow-hidden group">
            <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-8" novalidate>
                @csrf

                <div class="space-y-4" x-show="!recovery">
                    <label for="code" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-center block">6místný ověřovací kód</label>
                    <div class="relative group/input">
                        <div class="input-icon group-focus-within/input:text-primary">
                            <i class="fa-light fa-fingerprint text-xl"></i>
                        </div>
                        <input id="code" type="text" name="code" inputmode="numeric" autofocus autocomplete="one-time-code"
                               placeholder="000 000"
                               class="w-full input-with-icon bg-white/5 border {{ $errors->has('code') ? 'border-rose-500/40 shadow-[0_0_15px_rgba(244,63,94,0.1)]' : 'border-white/10' }} rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white/10 transition-all duration-300 font-black text-white placeholder-slate-700 outline-none tracking-[0.5em] text-center text-3xl py-6">
                    </div>
                    @error('code')
                        <div class="flex items-center justify-center gap-2 text-rose-400 mt-2 animate-shake">
                            <i class="fa-light fa-circle-exclamation text-[10px]"></i>
                            <p class="text-[10px] font-bold tracking-wide">{{ $message }}</p>
                        </div>
                    @enderror
                    <p class="text-[10px] text-slate-400 font-medium text-center italic">Otevřete Google Authenticator nebo podobnou aplikaci.</p>
                </div>

                <div class="space-y-4" x-show="recovery" x-cloak>
                    <label for="recovery_code" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 text-center block">Záchranný kód</label>
                    <div class="relative group/input">
                        <div class="input-icon group-focus-within/input:text-primary">
                            <i class="fa-light fa-key-skeleton text-xl"></i>
                        </div>
                        <input id="recovery_code" type="text" name="recovery_code" autocomplete="one-time-code"
                               placeholder="abcde-fghij"
                               class="w-full input-with-icon bg-white/5 border {{ $errors->has('recovery_code') ? 'border-rose-500/40 shadow-[0_0_15px_rgba(244,63,94,0.1)]' : 'border-white/10' }} rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white/10 transition-all duration-300 font-mono font-bold text-white placeholder-slate-700 outline-none text-center text-xl py-6 uppercase">
                    </div>
                    @error('recovery_code')
                        <div class="flex items-center justify-center gap-2 text-rose-400 mt-2 animate-shake">
                            <i class="fa-light fa-circle-exclamation text-[10px]"></i>
                            <p class="text-[10px] font-bold tracking-wide">{{ $message }}</p>
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full py-5 rounded-2xl text-base btn-glow group/btn">
                    <span class="relative z-10 flex items-center justify-center gap-3">
                        Ověřit a vstoupit
                        <i class="fa-light fa-unlock-keyhole group-hover/btn:scale-110 transition-transform duration-500"></i>
                    </span>
                </button>

                <div class="text-center pt-2">
                    <button type="button" class="auth-link text-[10px] flex items-center justify-center gap-2 mx-auto"
                            x-show="!recovery" @click="recovery = true; $nextTick(() => { $refs.recovery_code?.focus() })">
                        <i class="fa-light fa-life-ring text-primary"></i>
                        Použít záchranný kód
                    </button>

                    <button type="button" class="auth-link text-[10px] flex items-center justify-center gap-2 mx-auto"
                            x-show="recovery" x-cloak @click="recovery = false; $nextTick(() => { $refs.code?.focus() })">
                        <i class="fa-light fa-mobile-notch text-primary"></i>
                        Použít ověřovací kód
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-12 text-center animate-fade-in" style="animation-delay: 0.4s">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-rose-500 transition-colors">
                    Zrušit a odhlásit se
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
