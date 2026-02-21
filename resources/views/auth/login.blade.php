@extends('layouts.public')

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center section-padding bg-slate-50">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-10">
            @if($branding['logo_path'] ?? null)
                <div class="w-20 h-20 bg-secondary rounded-club flex items-center justify-center mx-auto mb-6 shadow-lg border-2 border-white/10 p-3">
                    <img src="{{ asset('storage/' . $branding['logo_path']) }}" class="max-w-full max-h-full object-contain" alt="">
                </div>
            @endif
            <h1 class="text-3xl font-black uppercase tracking-tight text-secondary">Přihlášení</h1>
            <p class="text-slate-500 font-medium mt-2 italic">Vítejte zpět na palubovce!</p>
        </div>

        @if (session('status'))
            <div class="bg-success-50 border-l-4 border-success text-success-700 p-4 mb-6 rounded shadow-sm font-bold text-sm">
                {{ session('status') }}
            </div>
        @endif

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
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div class="space-y-2">
                    <label for="email" class="text-xs font-black uppercase tracking-widest text-slate-400">E-mailová adresa</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <x-heroicon-o-envelope class="w-5 h-5" />
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none">
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <label for="password" class="text-xs font-black uppercase tracking-widest text-slate-400">Heslo</label>
                        <a href="{{ route('password.request') }}" class="text-[10px] font-black uppercase tracking-widest text-primary hover:text-primary-hover transition-colors">
                            Zapomenuté heslo?
                        </a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <x-heroicon-o-lock-closed class="w-5 h-5" />
                        </div>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                               class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none">
                    </div>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center cursor-pointer group">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
                        <span class="ml-2 text-xs font-bold text-slate-500 group-hover:text-secondary transition-colors uppercase tracking-widest">Pamatovat si mě</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full py-4 shadow-lg hover:shadow-primary/20">
                    Vstoupit do hry
                </button>
            </form>
        </div>

        <div class="flex flex-col items-center space-y-6 mt-8">
            <a href="{{ url('/') }}" class="flex items-center space-x-2 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-primary transition-colors group">
                <x-heroicon-m-arrow-left class="w-4 h-4 group-hover:-translate-x-1 transition-transform" />
                <span>Zpět na web</span>
            </a>

            <p class="text-center text-slate-400 font-medium text-sm italic">
                Nemáte účet? Obraťte se na svého trenéra.
            </p>
        </div>
    </div>
</div>
@endsection
