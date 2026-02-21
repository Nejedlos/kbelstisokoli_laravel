@extends('layouts.public')

@section('content')
<div class="auth-gradient flex items-center justify-center py-16" x-data="{ recovery: false }">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-10">
            @if($branding['logo_path'] ?? null)
                <div class="w-20 h-20 bg-white/5 rounded-club flex items-center justify-center mx-auto mb-6 shadow-lg border border-white/10 p-3">
                    <img src="{{ asset('storage/' . $branding['logo_path']) }}" class="max-w-full max-h-full object-contain" alt="">
                </div>
            @else
                <div class="w-16 h-16 mx-auto mb-6 text-primary">
                    <i class="fa-solid fa-shield-halved text-5xl icon-bounce icon-glow"></i>
                </div>
            @endif
            <h1 class="auth-title">Zabezpečení účtu</h1>
            <p class="auth-sub" x-show="!recovery">Zadejte kód z vaší autentizační aplikace.</p>
            <p class="auth-sub" x-show="recovery">Zadejte některý ze svých záchranných kódů.</p>
        </div>

        @if ($errors->any())
            <div class="bg-rose-600/20 border-l-4 border-rose-500 text-rose-100 p-4 mb-6 rounded shadow-sm font-bold text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="glass-card p-8 border-t-4 border-primary text-white/90">
            <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-6">
                @csrf

                <div class="space-y-2" x-show="!recovery">
                    <label for="code" class="text-xs font-black uppercase tracking-widest text-slate-300">Ověřovací kód</label>
                    <div class="relative">
                        <div class="input-icon"><i class="fa-solid fa-shield-keyhole text-slate-400"></i></div>
                        <input id="code" type="text" name="code" inputmode="numeric" autofocus autocomplete="one-time-code"
                               class="w-full input-with-icon pr-4 py-3 bg-white/5 border border-white/10 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-white placeholder-slate-400 outline-none tracking-[0.5em] text-center text-2xl">
                    </div>
                    <p class="text-[10px] text-slate-300 font-medium text-center italic mt-2">Otevřete aplikaci (Google Authenticator, Authy…) a opište 6místný kód.</p>
                </div>

                <div class="space-y-2" x-show="recovery" x-cloak>
                    <label for="recovery_code" class="text-xs font-black uppercase tracking-widest text-slate-300">Záchranný kód</label>
                    <div class="relative">
                        <div class="input-icon"><i class="fa-solid fa-key text-slate-400"></i></div>
                        <input id="recovery_code" type="text" name="recovery_code" autocomplete="one-time-code"
                               class="w-full input-with-icon pr-4 py-3 bg-white/5 border border-white/10 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-white placeholder-slate-400 outline-none font-mono text-center">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full py-4 btn-glow uppercase tracking-widest">
                    <i class="fa-solid fa-unlock-keyhole mr-2"></i>
                    Ověřit a pokračovat
                </button>

                <div class="text-center pt-2">
                    <button type="button" class="auth-link"
                            x-show="!recovery" @click="recovery = true; $nextTick(() => { $refs.recovery_code?.focus() })">
                        <i class="fa-solid fa-key mr-1"></i>
                        Použít záchranný kód
                    </button>

                    <button type="button" class="auth-link"
                            x-show="recovery" x-cloak @click="recovery = false; $nextTick(() => { $refs.code?.focus() })">
                        <i class="fa-solid fa-shield-keyhole mr-1"></i>
                        Použít ověřovací kód
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
