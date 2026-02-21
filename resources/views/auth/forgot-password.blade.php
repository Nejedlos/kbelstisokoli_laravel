@extends('layouts.public')

@section('content')
<div class="auth-gradient flex items-center justify-center py-16">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-10">
            @if($branding['logo_path'] ?? null)
                <div class="w-20 h-20 bg-white/5 rounded-club flex items-center justify-center mx-auto mb-6 shadow-lg border border-white/10 p-3">
                    <img src="{{ asset('storage/' . $branding['logo_path']) }}" class="max-w-full max-h-full object-contain" alt="">
                </div>
            @else
                <div class="w-16 h-16 mx-auto mb-6 text-primary">
                    <i class="fa-solid fa-envelope-open-text text-5xl icon-bounce icon-glow"></i>
                </div>
            @endif
            <h1 class="auth-title">Zapomenuté heslo</h1>
            <p class="auth-sub">Pošleme vám bezpečný odkaz pro obnovu.</p>
        </div>

        @if (session('status'))
            <div class="bg-emerald-600/20 border-l-4 border-emerald-500 text-emerald-100 p-4 mb-6 rounded shadow-sm font-bold text-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="glass-card p-8 border-t-4 border-primary text-white/90">
            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                <div class="space-y-2">
                    <label for="email" class="text-xs font-black uppercase tracking-widest text-slate-300">E‑mailová adresa</label>
                    <div class="relative">
                        <div class="input-icon"><i class="fa-solid fa-envelope text-slate-400"></i></div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full input-with-icon pr-4 py-3 bg-white/5 border border-white/10 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-white placeholder-slate-400 outline-none">
                    </div>
                    @error('email') <p class="text-xs text-rose-200 font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full py-4 btn-glow uppercase tracking-widest">
                    <i class="fa-solid fa-paper-plane mr-2"></i>
                    Odeslat odkaz pro reset
                </button>

                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="auth-link">
                        <i class="fa-solid fa-arrow-left mr-1"></i>
                        Zpět na přihlášení
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
