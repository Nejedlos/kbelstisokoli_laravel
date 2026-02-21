@extends('layouts.public')

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center section-padding bg-slate-50" x-data="{ recovery: false }">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-10">
            @if($branding['logo_path'] ?? null)
                <div class="w-20 h-20 bg-secondary rounded-club flex items-center justify-center mx-auto mb-6 shadow-lg border-2 border-white/10 p-3 text-white">
                    <img src="{{ asset('storage/' . $branding['logo_path']) }}" class="max-w-full max-h-full object-contain" alt="">
                </div>
            @endif
            <h1 class="text-3xl font-black uppercase tracking-tight text-secondary">Zabezpečení účtu</h1>
            <p class="text-slate-500 font-medium mt-2 italic" x-show="!recovery">Zadejte ověřovací kód z vaší aplikace.</p>
            <p class="text-slate-500 font-medium mt-2 italic" x-show="recovery">Zadejte jeden z vašich záchranných kódů.</p>
        </div>

        @if ($errors->any())
            <div class="bg-danger-50 border-l-4 border-danger text-danger-700 p-4 mb-6 rounded shadow-sm font-bold text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card p-8 shadow-xl border-t-4 border-primary">
            <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-6">
                @csrf

                <div class="space-y-2" x-show="!recovery">
                    <label for="code" class="text-xs font-black uppercase tracking-widest text-slate-400">Ověřovací kód</label>
                    <input id="code" type="text" name="code" inputmode="numeric" autofocus autocomplete="one-time-code"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none tracking-[0.5em] text-center text-2xl">
                    <p class="text-[10px] text-slate-400 font-medium text-center italic mt-2">Otevřete aplikaci (např. Google Authenticator) a opište 6místný kód.</p>
                </div>

                <div class="space-y-2" x-show="recovery" x-cloak>
                    <label for="recovery_code" class="text-xs font-black uppercase tracking-widest text-slate-400">Záchranný kód</label>
                    <input id="recovery_code" type="text" name="recovery_code" autocomplete="one-time-code"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none font-mono text-center">
                </div>

                <button type="submit" class="btn btn-primary w-full py-4 shadow-lg hover:shadow-primary/20 uppercase tracking-widest">
                    Ověřit a pokračovat
                </button>

                <div class="text-center pt-2">
                    <button type="button" class="text-[10px] font-black uppercase tracking-widest text-primary hover:text-primary-hover transition-colors"
                            x-show="!recovery" @click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })">
                        Použít záchranný kód
                    </button>

                    <button type="button" class="text-[10px] font-black uppercase tracking-widest text-primary hover:text-primary-hover transition-colors"
                            x-show="recovery" x-cloak @click="recovery = false; $nextTick(() => { $refs.code.focus() })">
                        Použít ověřovací kód
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
