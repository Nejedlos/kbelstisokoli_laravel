@extends('layouts.auth')

@section('content')
<div class="animate-fade-in-down">
    <x-auth-header title="Nové heslo" subtitle="Nastavte si bezpečný přístup" icon="fa-lock-keyhole" />

    <div class="glass-card">
    <form method="POST" action="{{ route('password.update') }}" class="space-y-8" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="space-y-3 fi-fo-field">
            <label for="email" class="fi-fo-field-label ml-1">{{ __('Potvrďte e‑mail') }}</label>
            <div class="fi-input-wrp">
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" autofocus
                       class="fi-input">
            </div>
            @error('email')
                <div class="fi-error-message" style="display: block !important;">
                    <span>{{ $message }}</span>
                </div>
            @enderror
        </div>

        <div class="space-y-3 fi-fo-field">
            <label for="password" class="fi-fo-field-label ml-1">{{ __('Nové heslo') }}</label>
            <div class="fi-input-wrp" x-data="{ isPasswordRevealed: false }">
                <div class="fi-input-wrp-content-ctn">
                    <input id="password" x-bind:type="isPasswordRevealed ? 'text' : 'password'" name="password" autocomplete="new-password"
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

        <div class="space-y-3 fi-fo-field">
            <label for="password_confirmation" class="fi-fo-field-label ml-1">{{ __('Potvrzení hesla') }}</label>
            <div class="fi-input-wrp" x-data="{ isPasswordRevealed: false }">
                <div class="fi-input-wrp-content-ctn">
                    <input id="password_confirmation" x-bind:type="isPasswordRevealed ? 'text' : 'password'" name="password_confirmation" autocomplete="new-password"
                           placeholder="••••••••"
                           class="fi-input">
                </div>
                <div class="fi-input-wrp-suffix">
                    <button type="button" x-on:click="isPasswordRevealed = !isPasswordRevealed" class="fi-input-wrp-action px-3" :title="isPasswordRevealed ? 'Skrýt heslo' : 'Zobrazit heslo'">
                        <i class="fa-light" :class="isPasswordRevealed ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
            </div>
        </div>

        <button type="submit" class="fi-btn fi-color-primary w-full py-5 rounded-full text-base group/btn">
            <span class="relative z-10 flex items-center justify-center gap-3">
                {{ __('Aktualizovat heslo') }}
                <i class="fa-light fa-check-double group-hover/btn:scale-110 transition-transform duration-500"></i>
            </span>
        </button>
    </form>
</div>

    <x-auth-footer back-label="Zpět na přihlášení" :back-url="route('login')" />
</div>
@endsection
