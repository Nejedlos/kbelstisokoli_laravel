@extends('layouts.auth')

@section('content')
<div class="animate-fade-in-down">
    <!-- Header -->
    <x-auth-header
        :title="__('Potvrzení přístupu')"
        :subtitle="__('Před pokračováním prosím potvrďte své heslo')"
        icon="fa-lock-keyhole"
    />

    <div class="glass-card">
        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-8" novalidate>
            @csrf

            <div class="space-y-3">
                <label for="password" class="fi-fo-field-label ml-1">{{ __('Vaše heslo') }}</label>
                <div class="fi-input-wrp">
                    <input id="password" type="password" name="password" required autofocus autocomplete="current-password"
                           placeholder="••••••••"
                           class="fi-input">
                </div>
                @error('password')
                    <div class="fi-error-message" style="display: block !important;">
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <button type="submit" class="fi-btn fi-color-primary w-full py-5 rounded-2xl text-base group/btn">
                <span class="relative z-10 flex items-center justify-center gap-3">
                    {{ __('Potvrdit heslo') }}
                    <i class="fa-light fa-check-double group-hover/btn:scale-110 transition-transform duration-500"></i>
                </span>
            </button>
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
