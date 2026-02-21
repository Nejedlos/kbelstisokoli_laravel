@extends('layouts.public')

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center section-padding bg-slate-50">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-10">
            @if($branding['logo_path'] ?? null)
                <div class="w-20 h-20 bg-primary rounded-club flex items-center justify-center mx-auto mb-6 shadow-lg border-2 border-white/10 p-3 text-white">
                    <img src="{{ asset('storage/' . $branding['logo_path']) }}" class="max-w-full max-h-full object-contain" alt="">
                </div>
            @endif
            <h1 class="text-3xl font-black uppercase tracking-tight text-secondary">Nové heslo</h1>
            <p class="text-slate-500 font-medium mt-2 italic">Zadejte své nové přístupové heslo.</p>
        </div>

        <div class="card p-8 shadow-xl border-t-4 border-primary">
            <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="space-y-2">
                    <label for="email" class="text-xs font-black uppercase tracking-widest text-slate-400">E-mailová adresa</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none">
                    @error('email') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="password" class="text-xs font-black uppercase tracking-widest text-slate-400">Nové heslo</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none">
                    @error('password') <p class="text-xs text-danger-600 font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="text-xs font-black uppercase tracking-widest text-slate-400">Potvrzení nového hesla</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-club focus:ring-2 focus:ring-primary focus:border-primary transition-all font-bold text-secondary outline-none">
                </div>

                <button type="submit" class="btn btn-primary w-full py-4 shadow-lg hover:shadow-primary/20 uppercase tracking-widest">
                    Změnit heslo
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
