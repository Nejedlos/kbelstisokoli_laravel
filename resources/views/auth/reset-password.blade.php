@extends('layouts.auth')

@section('content')
<div class="animate-fade-in-down">
    <x-auth-header title="Nové heslo" subtitle="Nastavte si bezpečný přístup" icon="fa-lock-keyhole" />

    <div class="glass-card">
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

    <x-auth-footer back-label="Zpět na přihlášení" :back-url="route('login')" />
</div>
@endsection
