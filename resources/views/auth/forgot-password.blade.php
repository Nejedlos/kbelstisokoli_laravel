@extends('layouts.auth')

@section('content')
<div class="animate-fade-in-down">
    <x-auth-header title="Zapomenuté heslo" subtitle="Pošleme vám odkaz pro obnovu přístupu" icon="fa-key-skeleton" />

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
        <form method="POST" action="{{ route('password.email') }}" class="space-y-8" novalidate>
        @csrf

        <div class="space-y-3 fi-fo-field {{ $errors->has('email') ? 'ks-invalid' : '' }}">
            <label for="email" class="fi-fo-field-label ml-1">{{ __('Vaše e‑mailová adresa') }}</label>
            <div class="fi-input-wrp">
                <input id="email" type="email" name="email" value="{{ old('email') }}" autofocus
                       placeholder="jmeno@klub.cz"
                       class="fi-input">
            </div>
        </div>

        <button type="submit" class="fi-btn fi-color-primary w-full py-5 rounded-full text-base group/btn">
            <span class="relative z-10 flex items-center justify-center gap-3">
                {{ __('Odeslat instrukce') }}
                <i class="fa-light fa-chevron-right group-hover/btn:translate-x-1 transition-transform"></i>
            </span>
        </button>
    </form>
</div>

    <x-auth-footer back-label="Zpět na přihlášení" :back-url="route('login')" />
</div>
@endsection
