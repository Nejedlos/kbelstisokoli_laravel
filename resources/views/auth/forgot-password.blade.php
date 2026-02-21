@extends('layouts.public')

@section('content')
<div class="auth-gradient">
    <!-- Floating Background Objects -->
    <div class="floating-objects">
        <div class="floating-ball w-64 h-64 top-[-10%] left-[-5%] bg-primary"></div>
        <div class="floating-ball w-96 h-96 bottom-[-15%] right-[-10%] opacity-5"></div>
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
                    <i class="fa-duotone fa-solid fa-envelope-open-text text-6xl icon-bounce icon-glow"></i>
                </div>
            @endif
            <h1 class="auth-title tracking-tight">Zapomněli jste?</h1>
            <p class="auth-sub tracking-tight">Pošleme vám odkaz pro obnovu přístupu.</p>
        </div>

        @if (session('status'))
            <div class="glass-card !bg-emerald-500/10 border-emerald-500/30 text-emerald-200 p-5 mb-8 rounded-2xl flex items-center gap-4 animate-fade-in">
                <i class="fa-solid fa-paper-plane-launch text-xl"></i>
                <span class="font-bold text-sm">{{ session('status') }}</span>
            </div>
        @endif

        <div class="glass-card p-10 border-t-2 border-primary/50 relative overflow-hidden group">
            <form method="POST" action="{{ route('password.email') }}" class="space-y-8">
                @csrf

                <div class="space-y-3">
                    <label for="email" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Vaše e‑mailová adresa</label>
                    <div class="relative group/input">
                        <div class="input-icon group-focus-within/input:text-primary">
                            <i class="fa-solid fa-envelope text-lg"></i>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="jmeno@klub.cz"
                               class="w-full input-with-icon bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white/10 transition-all duration-300 font-bold text-white placeholder-slate-600 outline-none">
                    </div>
                    @error('email') <p class="text-[10px] text-rose-400 font-bold mt-2 ml-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full py-5 rounded-2xl text-base btn-glow group/btn">
                    <span class="relative z-10 flex items-center justify-center gap-3 font-black">
                        Odeslat instrukce
                        <i class="fa-solid fa-chevron-right group-hover/btn:translate-x-1 transition-transform"></i>
                    </span>
                </button>

                <div class="text-center pt-2">
                    <a href="{{ route('login') }}" class="auth-link text-[10px] flex items-center justify-center gap-2 mx-auto opacity-60 hover:opacity-100">
                        <i class="fa-solid fa-arrow-left"></i>
                        Zpět na přihlášení
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
