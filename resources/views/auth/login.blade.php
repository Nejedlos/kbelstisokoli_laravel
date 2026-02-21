@extends('layouts.public')

@section('content')
<div class="auth-gradient">
    <!-- Floating Background Objects -->
    <div class="floating-objects">
        <div class="floating-ball w-64 h-64 top-[-10%] left-[-5%]"></div>
        <div class="floating-ball w-96 h-96 bottom-[-15%] right-[-10%] opacity-5"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo/Header -->
        <div class="text-center mb-12 animate-fade-in-down">
            @if($branding['logo_path'] ?? null)
                <div class="w-24 h-24 bg-white/5 backdrop-blur-md rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl border border-white/10 p-4 transition-transform hover:scale-105 duration-500">
                    <img src="{{ asset('storage/' . $branding['logo_path']) }}" class="max-w-full max-h-full object-contain filter drop-shadow-lg" alt="{{ $branding['club_name'] }}">
                </div>
            @else
                <div class="w-20 h-20 mx-auto mb-8 text-primary flex items-center justify-center">
                    <i class="fa-duotone fa-solid fa-basketball-hoop text-6xl icon-bounce icon-glow"></i>
                </div>
            @endif
            <h1 class="auth-title">Vítejte zpět</h1>
            <p class="auth-sub tracking-tight">Vstupte na palubovku ###TEAM_NAME###</p>
        </div>

        @if (session('status'))
            <div class="glass-card !bg-emerald-500/10 border-emerald-500/30 text-emerald-200 p-5 mb-8 rounded-2xl flex items-center gap-4 animate-fade-in">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <span class="font-bold text-sm">{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="glass-card !bg-rose-500/10 border-rose-500/30 text-rose-200 p-5 mb-8 rounded-2xl animate-shake">
                <div class="flex items-center gap-4 mb-2">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                    <span class="font-bold text-sm underline decoration-rose-500/50 underline-offset-4">Chyba při přihlášení</span>
                </div>
                <ul class="list-none text-xs font-medium opacity-80 pl-9">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="glass-card p-10 border-t-2 border-primary/50 relative overflow-hidden group">
            <!-- Decorative corner accent -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 blur-3xl -mr-16 -mt-16 group-hover:bg-primary/10 transition-colors duration-700"></div>

            <form method="POST" action="{{ route('login') }}" class="space-y-8">
                @csrf

                <div class="space-y-3">
                    <label for="email" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 ml-1">E‑mailová adresa</label>
                    <div class="relative group/input">
                        <div class="input-icon group-focus-within/input:text-primary">
                            <i class="fa-solid fa-envelope-open text-lg"></i>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="jmeno@klub.cz"
                               class="w-full input-with-icon bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white/10 transition-all duration-300 font-bold text-white placeholder-slate-600 outline-none">
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between items-center px-1">
                        <label for="password" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Heslo</label>
                        <a href="{{ route('password.request') }}" class="auth-link text-[10px]">Zapomněli jste?</a>
                    </div>
                    <div class="relative group/input">
                        <div class="input-icon group-focus-within/input:text-primary">
                            <i class="fa-solid fa-lock-keyhole text-lg"></i>
                        </div>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                               placeholder="••••••••"
                               class="w-full input-with-icon bg-white/5 border border-white/10 rounded-2xl focus:ring-4 focus:ring-primary/20 focus:border-primary focus:bg-white/10 transition-all duration-300 font-bold text-white placeholder-slate-600 outline-none">
                    </div>
                </div>

                <div class="flex items-center px-1">
                    <label class="flex items-center cursor-pointer group/check">
                        <div class="relative flex items-center justify-center">
                            <input type="checkbox" name="remember" class="peer sr-only">
                            <div class="w-5 h-5 bg-white/5 border border-white/10 rounded-md peer-checked:bg-primary peer-checked:border-primary transition-all"></div>
                            <i class="fa-solid fa-check absolute text-[10px] text-white opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                        </div>
                        <span class="ml-3 text-[10px] font-black text-slate-400 group-hover/check:text-white transition-colors uppercase tracking-widest">Pamatovat si mě</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full py-5 rounded-2xl text-base btn-glow group/btn">
                    <span class="relative z-10 flex items-center justify-center gap-3">
                        Vstoupit do hry
                        <i class="fa-solid fa-arrow-right-long group-hover/btn:translate-x-2 transition-transform duration-500"></i>
                    </span>
                </button>
            </form>
        </div>

        <div class="mt-12 flex flex-col items-center gap-8 animate-fade-in" style="animation-delay: 0.4s">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3 px-6 py-3 rounded-full bg-white/5 border border-white/5 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-white hover:bg-white/10 transition-all duration-300 group">
                <i class="fa-solid fa-house-chimney text-primary group-hover:scale-110 transition-transform"></i>
                <span>Zpět na úvodní stránku</span>
            </a>

            <div class="flex items-center gap-4 text-slate-600">
                <div class="h-px w-8 bg-white/5"></div>
                <p class="text-[10px] font-black uppercase tracking-widest italic">###TEAM_SHORT### Arena</p>
                <div class="h-px w-8 bg-white/5"></div>
            </div>
        </div>
    </div>
</div>
@endsection
