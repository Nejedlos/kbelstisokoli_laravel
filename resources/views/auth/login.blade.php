@extends('layouts.auth')

@section('content')
<h1 style="background: red; color: white; padding: 20px; text-align: center; font-size: 30px; margin-bottom: 20px;">DEBUG: TATO ŠABLONA JE UPRAVENA (RESOURCES/VIEWS/AUTH/LOGIN.BLADE.PHP)</h1>
<div class="animate-fade-in-down">
    <x-auth-header title="Vítejte zpět" subtitle="Vstupte na palubovku vaší arény" />

    @if (session('status'))
            <div class="glass-card !bg-emerald-500/10 border-emerald-500/30 text-emerald-200 p-6 mb-8 rounded-3xl flex items-center gap-4 animate-fade-in shadow-lg shadow-emerald-500/5">
                <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                    <i class="fa-light fa-circle-check text-xl"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase tracking-widest font-black opacity-50 mb-0.5">Skvělá zpráva</span>
                    <span class="font-bold text-sm leading-tight text-white/90">{{ session('status') }}</span>
                </div>
            </div>
        @endif

        <div class="glass-card">
            <form method="POST" action="{{ route('login') }}" class="space-y-8" novalidate>
                @csrf

                <div class="space-y-3">
                    <label for="email" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">E‑mailová adresa</label>
                    <div class="relative group/input">
                        <div class="input-icon group-focus-within/input:text-primary">
                            <i class="fa-light fa-envelope-open text-lg"></i>
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

                <div class="space-y-3">
                    <div class="flex justify-between items-center px-1">
                        <label for="password" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Heslo</label>
                        <a href="{{ route('password.request') }}" class="auth-link text-[10px]">Zapomněli jste?</a>
                    </div>
                    <div class="relative group/input">
                        <div class="input-icon group-focus-within/input:text-primary">
                            <i class="fa-light fa-lock-keyhole text-lg"></i>
                        </div>
                        <input id="password" type="password" name="password" autocomplete="current-password"
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

                <div class="flex items-center px-1">
                    <label class="flex items-center cursor-pointer group/check">
                        <div class="relative flex items-center justify-center">
                            <input type="checkbox" name="remember" class="peer sr-only">
                            <div class="w-5 h-5 bg-white/5 border border-white/10 rounded-md peer-checked:bg-primary peer-checked:border-primary transition-all"></div>
                            <i class="fa-light fa-check absolute text-[10px] text-white opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                        </div>
                        <span class="ml-3 text-[10px] font-black text-slate-400 group-hover/check:text-white transition-colors uppercase tracking-widest">Pamatovat si mě</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full py-5 rounded-2xl text-base btn-glow group/btn">
                    <span class="relative z-10 flex items-center justify-center gap-3">
                        Vstoupit do hry
                        <i class="fa-light fa-arrow-right-long group-hover/btn:translate-x-2 transition-transform duration-500"></i>
                    </span>
                </button>
            </form>
        </div>

    <x-auth-footer />
</div>
@endsection
