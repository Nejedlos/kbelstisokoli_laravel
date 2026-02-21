@extends('layouts.public')

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center section-padding bg-slate-50">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-10">
            @if($branding['logo_path'] ?? null)
                <div class="w-20 h-20 bg-secondary rounded-club flex items-center justify-center mx-auto mb-6 shadow-lg border-2 border-white/10 p-3 text-white">
                    <img src="{{ asset('storage/' . $branding['logo_path']) }}" class="max-w-full max-h-full object-contain" alt="">
                </div>
            @endif
            <h1 class="text-3xl font-black uppercase tracking-tight text-secondary">Zapomenuté heslo</h1>
            <p class="text-slate-500 font-medium mt-2 italic">Pošleme vám instrukce k resetu hesla.</p>
        </div>

        @if (session('status'))
            <div class="bg-success-50 border-l-4 border-success text-success-700 p-4 mb-6 rounded shadow-sm font-bold text-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="card p-8 shadow-xl">
            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                <div class="space-y-2">
                    <label for="email" class="text-xs font-black uppercase tracking-widest text-slate-400">E-mailová adresa</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none">
                    @error('email') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full py-4 shadow-lg hover:shadow-primary/20 uppercase tracking-widest">
                    Odeslat odkaz pro reset
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-secondary transition-colors">
                        Zpět na přihlášení
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
