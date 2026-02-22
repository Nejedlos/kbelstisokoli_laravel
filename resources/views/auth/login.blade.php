@extends('layouts.auth')

@section('content')
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

            <div class="space-y-3 fi-fo-field">
                <label for="email" class="fi-fo-field-label ml-1">{{ __('E‑mailová adresa') }}</label>
                <div class="fi-input-wrp">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autofocus
                           placeholder="jmeno@klub.cz"
                           class="fi-input">
                </div>
                @error('email')
                    <div class="fi-error-message" style="display: block !important;">
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div class="space-y-3 fi-fo-field">
                <div class="flex justify-between items-center px-1">
                    <label for="password" class="fi-fo-field-label">{{ __('Heslo') }}</label>
                    <a href="{{ route('password.request') }}" class="fi-link text-[10px]">{{ __('Zapomněli jste?') }}</a>
                </div>
                <div class="fi-input-wrp">
                    <input id="password" type="password" name="password" autocomplete="current-password"
                           placeholder="••••••••"
                           class="fi-input">
                </div>
                @error('password')
                    <div class="fi-error-message" style="display: block !important;">
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div class="flex items-center px-1">
                <label class="flex items-center cursor-pointer group/check">
                    <div class="relative flex items-center justify-center">
                        <input type="checkbox" name="remember" class="fi-checkbox-input peer sr-only">
                        <div class="w-5 h-5 bg-white/5 border border-white/10 rounded-md peer-checked:bg-primary peer-checked:border-primary transition-all"></div>
                        <i class="fa-light fa-check absolute text-[10px] text-white opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                    </div>
                    <span class="ml-3 text-[10px] font-black text-slate-400 group-hover/check:text-white transition-colors uppercase tracking-widest">{{ __('Pamatovat si mě') }}</span>
                </label>
            </div>

            <button type="submit" class="fi-btn fi-color-primary w-full py-5 rounded-2xl text-base group/btn">
                <span class="relative z-10 flex items-center justify-center gap-3">
                    {{ __('Vstoupit do hry') }}
                    <i class="fa-light fa-arrow-right-long group-hover/btn:translate-x-2 transition-transform duration-500"></i>
                </span>
            </button>
        </form>
    </div>

    <x-auth-footer />
</div>
@endsection
