@extends('layouts.auth')

@section('content')
<div class="animate-fade-in-down">
    <x-auth-header title="Zapomenuté heslo" subtitle="Pošleme vám odkaz pro obnovu přístupu" icon="fa-key-skeleton" />

    @if (session('status'))
    <div class="glass-card !bg-emerald-500/10 border-emerald-500/30 text-emerald-200 p-6 mb-8 rounded-3xl flex items-center gap-4 animate-fade-in shadow-lg shadow-emerald-500/5">
        <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
            <i class="fa-light fa-paper-plane-launch text-xl"></i>
        </div>
        <div class="flex flex-col">
            <span class="text-[10px] uppercase tracking-widest font-black opacity-50 mb-0.5">E-mail odeslán</span>
            <span class="font-bold text-sm leading-tight text-white/90">{{ session('status') }}</span>
        </div>
    </div>
@endif

    <div class="glass-card">
        <form method="POST" action="{{ route('password.email') }}" class="space-y-8" novalidate>
        @csrf

        <div class="space-y-3">
            <label for="email" class="fi-fo-field-label ml-1">{{ __('Vaše e‑mailová adresa') }}</label>
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
