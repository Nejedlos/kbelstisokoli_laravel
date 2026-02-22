@extends('layouts.auth')

@section('content')
<div x-data="{ recovery: false }">
    <!-- Header -->
    <x-auth-header
        :title="__('Osobní kontrola')"
        :subtitle="__('Ukaž týmovou kartu (6místný kód)')"
        icon="fa-shield-check"
    />

    <div class="glass-card">
        <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-8" novalidate>
            @csrf

            <div class="space-y-4" x-show="!recovery">
                <label for="code" class="fi-fo-field-label block text-center">{{ __('6místný ověřovací kód') }}</label>
                <div class="relative group/input">
                    <input id="code" type="text" name="code" inputmode="numeric" autofocus autocomplete="one-time-code"
                           placeholder="000 000"
                           class="fi-input-wrp w-full bg-white border {{ $errors->has('code') ? 'border-rose-500' : 'border-slate-200' }} rounded-full focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all duration-300 font-black text-slate-900 placeholder-slate-300 outline-none tracking-[0.5em] text-center text-3xl py-6">
                </div>
                @if ($errors->has('code'))
                    <p class="fi-error-message block text-center" style="display: block !important;">{{ $errors->first('code') }}</p>
                @endif
                <p class="text-[10px] text-white/40 font-medium text-center italic">{{ __('Otevřete Google Authenticator nebo podobnou aplikaci.') }}</p>
            </div>

            <div class="space-y-4" x-show="recovery" x-cloak>
                <label for="recovery_code" class="fi-fo-field-label block text-center">{{ __('Záchranný kód') }}</label>
                <div class="relative group/input">
                    <input id="recovery_code" type="text" name="recovery_code" autocomplete="one-time-code"
                           placeholder="abcde-fghij"
                           class="fi-input-wrp w-full bg-white border {{ $errors->has('recovery_code') ? 'border-rose-500' : 'border-slate-200' }} rounded-full focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all duration-300 font-mono font-bold text-slate-900 placeholder-slate-300 outline-none text-center text-xl py-6 uppercase">
                </div>
                @if ($errors->has('recovery_code'))
                    <p class="fi-error-message block text-center" style="display: block !important;">{{ $errors->first('recovery_code') }}</p>
                @endif
            </div>

            <button type="submit" class="fi-btn fi-color-primary w-full py-5 rounded-full text-base group/btn">
                <span class="relative z-10 flex items-center justify-center gap-3">
                    {{ __('Vstoupit na palubovku') }}
                    <i class="fa-light fa-unlock-keyhole group-hover/btn:scale-110 transition-transform duration-500"></i>
                </span>
            </button>

            <div class="text-center pt-2">
                <button type="button" class="fi-link flex items-center justify-center gap-2 mx-auto"
                        x-show="!recovery" @click="recovery = true; $nextTick(() => { document.getElementById('recovery_code')?.focus() })">
                    <i class="fa-light fa-life-ring"></i>
                    {{ __('Použít záchranný kód') }}
                </button>

                <button type="button" class="fi-link flex items-center justify-center gap-2 mx-auto"
                        x-show="recovery" x-cloak @click="recovery = false; $nextTick(() => { document.getElementById('code')?.focus() })">
                    <i class="fa-light fa-mobile-notch"></i>
                    {{ __('Použít ověřovací kód') }}
                </button>
            </div>
        </form>
    </div>

    <div class="mt-8">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="auth-footer-link-primary flex items-center justify-center gap-2 mx-auto opacity-60 hover:opacity-100 text-xs">
                {{ __('Zrušit a odhlásit se') }}
            </button>
        </form>
    </div>

    <x-auth-footer :show-back="false" />
</div>
@endsection
