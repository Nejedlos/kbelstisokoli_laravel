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
            <i class="fa-duotone fa-light fa-lock-keyhole text-6xl icon-bounce icon-glow"></i>
        </div>
    @endif
    <h1 class="auth-title">Potvrzení přístupu</h1>
    <p class="auth-sub tracking-tight">Před pokračováním prosím potvrďte své heslo</p>
</div>

<div class="glass-card p-10 border-t-2 border-primary/50 relative overflow-hidden group">
    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-8" novalidate>
        @csrf

        <div class="space-y-3">
            <label for="password" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Vaše heslo</label>
            <div class="relative group/input">
                <div class="input-icon group-focus-within/input:text-primary">
                    <i class="fa-light fa-keyhole text-lg"></i>
                </div>
                <input id="password" type="password" name="password" required autofocus autocomplete="current-password"
                       placeholder="••••••••"
                       class="w-full input-with-icon bg-white/5 border {{ $errors->has('password') ? 'border-rose-500/40 shadow-[0_0_15px_rgba(244,63,94,0.1)]' : 'border-white/10' }} rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white/10 transition-all duration-300 font-bold text-white placeholder-slate-600 outline-none">
            </div>
            @error('password')
                <div class="flex items-center gap-2 text-rose-400 mt-2 ml-1 animate-shake">
                    <i class="fa-light fa-circle-exclamation text-[10px]"></i>
                    <p class="text-[10px] font-bold tracking-wide">{{ $message }}</p>
                </div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-full py-5 rounded-2xl text-base btn-glow group/btn">
            <span class="relative z-10 flex items-center justify-center gap-3">
                Potvrdit heslo
                <i class="fa-light fa-check-double group-hover/btn:scale-110 transition-transform duration-500"></i>
            </span>
        </button>
    </form>
</div>

<div class="mt-12 text-center animate-fade-in" style="animation-delay: 0.4s">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-rose-400 transition-colors">
            Zrušit a odhlásit se
        </button>
    </form>
</div>
@endsection
