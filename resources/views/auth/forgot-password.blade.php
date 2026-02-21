@extends('layouts.auth')

@section('content')
<!-- Header -->
<div class="text-center mb-12 animate-fade-in-down">
    @if($branding['logo_path'] ?? null)
        <div class="w-24 h-24 bg-white/5 backdrop-blur-md rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl border border-white/10 p-4 transition-transform hover:scale-105 duration-500">
            <img src="{{ asset('storage/' . $branding['logo_path']) }}" class="max-w-full max-h-full object-contain filter drop-shadow-lg" alt="{{ $branding['club_name'] }}">
        </div>
    @else
        <div class="w-20 h-20 mx-auto mb-8 text-primary flex items-center justify-center">
            <i class="fa-duotone fa-light fa-envelope-open-text text-6xl icon-bounce icon-glow"></i>
        </div>
    @endif
    <h1 class="auth-title tracking-tight">Zapomněli jste?</h1>
    <p class="auth-sub tracking-tight">Pošleme vám odkaz pro obnovu přístupu.</p>
</div>

@if (session('status'))
    <div class="glass-card !bg-emerald-500/10 border-emerald-500/30 text-emerald-200 p-6 mb-8 rounded-3xl flex items-center gap-4 animate-fade-in shadow-lg shadow-emerald-500/5">
        <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
            <i class="fa-light fa-paper-plane-launch text-xl"></i>
        </div>
        <div class="flex flex-col">
            <span class="text-[10px] uppercase tracking-widest font-black opacity-50 mb-0.5">E-mail odeslán</span>
            <span class="font-bold text-sm leading-tight text-white/90">{{ session('status') }}</span>
        </div>
    </div>
@endif

<div class="glass-card p-10 border-t-2 border-primary/50 relative overflow-hidden group">
    <form method="POST" action="{{ route('password.email') }}" class="space-y-8" novalidate>
        @csrf

        <div class="space-y-3">
            <label for="email" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Vaše e‑mailová adresa</label>
            <div class="relative group/input">
                <div class="input-icon group-focus-within/input:text-primary">
                    <i class="fa-light fa-envelope text-lg"></i>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autofocus
                       placeholder="jmeno@klub.cz"
                       class="w-full input-with-icon bg-white/5 border {{ $errors->has('email') ? 'border-rose-500/40 shadow-[0_0_15px_rgba(244,63,94,0.1)]' : 'border-white/10' }} rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white/10 transition-all duration-300 font-bold text-white placeholder-slate-600 outline-none">
            </div>
            @error('email')
                <div class="flex items-center gap-2 text-rose-400 mt-2 ml-1 animate-shake">
                    <i class="fa-light fa-circle-exclamation text-[10px]"></i>
                    <p class="text-[10px] font-bold tracking-wide">{{ $message }}</p>
                </div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-full py-5 rounded-2xl text-base btn-glow group/btn">
            <span class="relative z-10 flex items-center justify-center gap-3 font-black">
                Odeslat instrukce
                <i class="fa-light fa-chevron-right group-hover/btn:translate-x-1 transition-transform"></i>
            </span>
        </button>

        <div class="text-center pt-2">
            <a href="{{ route('login') }}" class="auth-link text-[10px] flex items-center justify-center gap-2 mx-auto opacity-60 hover:opacity-100">
                <i class="fa-light fa-arrow-left"></i>
                Zpět na přihlášení
            </a>
        </div>
    </form>
</div>
@endsection
