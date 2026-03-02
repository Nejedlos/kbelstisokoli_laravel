@extends('layouts.auth')

@section('content')
<div class="animate-fade-in-down">
    <x-auth-header title="Vítejte zpět" subtitle="Vstupte na palubovku vaší arény" />

    @if (session('status'))
        <x-auth-alert type="success" :message="session('status')" />
    @endif

    @if ($errors->any())
        @php
            $filteredErrors = collect($errors->all())->filter(fn($error) => trim($error) !== '')->all();
        @endphp
        @if (!empty($filteredErrors))
            <x-auth-alert type="error">
                @foreach ($filteredErrors as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </x-auth-alert>
        @endif
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
                <div class="fi-input-wrp" x-data="{ isPasswordRevealed: false }">
                    <div class="fi-input-wrp-content-ctn">
                        <input id="password" x-bind:type="isPasswordRevealed ? 'text' : 'password'" name="password" autocomplete="current-password"
                               placeholder="••••••••"
                               class="fi-input">
                    </div>
                    <div class="fi-input-wrp-suffix">
                        <button type="button" x-on:click="isPasswordRevealed = !isPasswordRevealed" class="fi-input-wrp-action px-3" :title="isPasswordRevealed ? 'Skrýt heslo' : 'Zobrazit heslo'">
                            <i class="fa-light" :class="isPasswordRevealed ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
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

            <button type="submit" class="fi-btn fi-color-primary w-full py-5 rounded-full text-base group/btn">
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
