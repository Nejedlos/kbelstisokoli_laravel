@extends('layouts.auth')

@section('content')
<!-- Header -->
<div class="text-center mb-12 animate-fade-in-down">
    @if($branding['logo_path'] ?? null)
        <div class="w-24 h-24 bg-white/5 backdrop-blur-md rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl border border-white/10 p-4 transition-transform hover:scale-105 duration-500">
            <img src="{{ asset('storage/' . $branding['logo_path']) }}" class="max-w-full max-h-full object-contain filter drop-shadow-lg" alt="{{ $branding['club_name'] }}">
        </div>
    @else
        <div class="auth-icon-container">
            <i class="fa-duotone fa-light fa-key-skeleton text-5xl text-primary icon-bounce icon-glow"></i>
        </div>
    @endif
    <h1 class="auth-title tracking-tight">Nové heslo</h1>
    <p class="auth-sub tracking-tight">Nastavte si bezpečný přístup</p>
</div>

<div class="glass-card p-10 border-t-2 border-primary/50 relative overflow-hidden group">
    <!-- Decorative corner accent -->
    <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 blur-3xl -mr-16 -mt-16 group-hover:bg-primary/10 transition-colors duration-700"></div>
    <form method="POST" action="{{ route('password.update') }}" class="space-y-8" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="space-y-3">
            <label for="email" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Potvrďte e‑mail</label>
            <div class="relative group/input">
                <div class="input-icon group-focus-within/input:text-primary">
                    <i class="fa-light fa-envelope text-lg"></i>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" autofocus
                       class="w-full input-with-icon bg-white/5 border {{ $errors->has('email') ? 'border-rose-500/40 shadow-[0_0_15px_rgba(244,63,94,0.1)]' : 'border-white/10' }} rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white/10 transition-all duration-300 font-bold text-white outline-none">
            </div>
            @error('email')
                <div class="flex items-center gap-2 text-rose-400 mt-2 ml-1 animate-shake">
                    <i class="fa-light fa-circle-exclamation text-[10px]"></i>
                    <p class="text-[10px] font-bold tracking-wide">{{ $message }}</p>
                </div>
            @enderror
        </div>

        <div class="space-y-3">
            <label for="password" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Nové heslo</label>
            <div class="relative group/input">
                <div class="input-icon group-focus-within/input:text-primary">
                    <i class="fa-light fa-lock-keyhole text-lg"></i>
                </div>
                <input id="password" type="password" name="password" autocomplete="new-password"
                       placeholder="••••••••"
                       class="w-full input-with-icon bg-white/5 border {{ $errors->has('password') ? 'border-rose-500/40 shadow-[0_0_15px_rgba(244,63,94,0.1)]' : 'border-white/10' }} rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white/10 transition-all duration-300 font-bold text-white outline-none">
            </div>
            @error('password')
                <div class="flex items-center gap-2 text-rose-400 mt-2 ml-1 animate-shake">
                    <i class="fa-light fa-circle-exclamation text-[10px]"></i>
                    <p class="text-[10px] font-bold tracking-wide">{{ $message }}</p>
                </div>
            @enderror
        </div>

        <div class="space-y-3">
            <label for="password_confirmation" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Potvrzení hesla</label>
            <div class="relative group/input">
                <div class="input-icon group-focus-within/input:text-primary">
                    <i class="fa-light fa-shield-check text-lg"></i>
                </div>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                       placeholder="••••••••"
                       class="w-full input-with-icon bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white/10 transition-all duration-300 font-bold text-white outline-none">
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-full py-5 rounded-2xl text-base btn-glow group/btn">
            <span class="relative z-10 flex items-center justify-center gap-3 font-black">
                Aktualizovat heslo
                <i class="fa-light fa-check-double group-hover/btn:scale-110 transition-transform duration-500"></i>
            </span>
        </button>
    </form>
</div>

<div class="mt-12 text-center animate-fade-in space-y-6" style="animation-delay: 0.4s">
    <div class="flex items-center justify-center gap-4 text-slate-600">
        <div class="h-px w-8 bg-white/5"></div>
        <p class="text-[9px] font-black uppercase tracking-[0.3em] italic opacity-40">{{ $branding['club_short_name'] }} Arena</p>
        <div class="h-px w-8 bg-white/5"></div>
    </div>
</div>
@endsection
