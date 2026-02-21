@extends('layouts.public')

@section('content')
<div class="auth-gradient">
    <!-- Floating Background Objects -->
    <div class="floating-objects">
        <div class="floating-ball w-64 h-64 top-[-10%] right-[-5%] bg-accent"></div>
        <div class="floating-ball w-96 h-96 bottom-[-15%] left-[-10%] opacity-5"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <!-- Header -->
        <div class="text-center mb-12 animate-fade-in-down">
            @if($branding['logo_path'] ?? null)
                <div class="w-24 h-24 bg-white/5 backdrop-blur-md rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl border border-white/10 p-4 transition-transform hover:scale-105 duration-500">
                    <img src="{{ asset('storage/' . $branding['logo_path']) }}" class="max-w-full max-h-full object-contain filter drop-shadow-lg" alt="{{ $branding['club_name'] }}">
                </div>
            @else
                <div class="w-16 h-16 mx-auto mb-8 text-primary flex items-center justify-center">
                    <i class="fa-duotone fa-solid fa-key-skeleton text-6xl icon-bounce icon-glow"></i>
                </div>
            @endif
            <h1 class="auth-title tracking-tight">Nové heslo</h1>
            <p class="auth-sub tracking-tight">Nastavte si bezpečný přístup</p>
        </div>

        <div class="glass-card p-10 border-t-2 border-primary/50 relative overflow-hidden group">
            <form method="POST" action="{{ route('password.update') }}" class="space-y-8">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="space-y-3">
                    <label for="email" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Potvrďte e‑mail</label>
                    <div class="relative group/input">
                        <div class="input-icon group-focus-within/input:text-primary">
                            <i class="fa-solid fa-envelope text-lg"></i>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                               class="w-full input-with-icon bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white/10 transition-all duration-300 font-bold text-white outline-none">
                    </div>
                    @error('email') <p class="text-[10px] text-rose-400 font-bold mt-2 ml-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-3">
                    <label for="password" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Nové heslo</label>
                    <div class="relative group/input">
                        <div class="input-icon group-focus-within/input:text-primary">
                            <i class="fa-solid fa-lock-keyhole text-lg"></i>
                        </div>
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                               placeholder="••••••••"
                               class="w-full input-with-icon bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white/10 transition-all duration-300 font-bold text-white outline-none">
                    </div>
                    @error('password') <p class="text-[10px] text-rose-400 font-bold mt-2 ml-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-3">
                    <label for="password_confirmation" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">Potvrzení hesla</label>
                    <div class="relative group/input">
                        <div class="input-icon group-focus-within/input:text-primary">
                            <i class="fa-solid fa-shield-check text-lg"></i>
                        </div>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                               placeholder="••••••••"
                               class="w-full input-with-icon bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white/10 transition-all duration-300 font-bold text-white outline-none">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full py-5 rounded-2xl text-base btn-glow group/btn">
                    <span class="relative z-10 flex items-center justify-center gap-3 font-black">
                        Aktualizovat heslo
                        <i class="fa-solid fa-check-double group-hover/btn:scale-110 transition-transform duration-500"></i>
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
