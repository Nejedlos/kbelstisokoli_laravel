@extends('layouts.member')

@section('content')
<div class="container-fluid px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <header class="mb-8">
            <h1 class="text-3xl font-black uppercase tracking-tight text-secondary">
                {{ __('Výsledky vyhledávání') }}
            </h1>
            <p class="text-slate-500 mt-2">
                Hledaný výraz: <span class="font-bold text-slate-900">"{{ $query }}"</span>
            </p>
        </header>

        <div class="space-y-6">
            @if($results->isEmpty())
                <div class="bg-white rounded-xl shadow-sm p-12 text-center border border-slate-100">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fa-light fa-magnifying-glass text-3xl text-slate-300"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 mb-2">Nebyly nalezeny žádné relevantní cíle</h2>
                    <p class="text-slate-500 max-w-md mx-auto">
                        Zkuste svůj dotaz popsat jinými slovy nebo zkontrolujte, zda nemáte v dotazu překlep.
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('member.dashboard') }}" class="btn btn-outline">
                            Zpět na nástěnku
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-primary/5 rounded-xl p-4 border border-primary/10 flex items-center gap-4 mb-8">
                    <div class="w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center shrink-0 shadow-lg">
                        <i class="fa-light fa-sparkles"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-primary">AI Doporučení</h3>
                        <p class="text-xs text-slate-600">Na základě vašeho dotazu jsme vybrali nejpravděpodobnější místa, kam byste se mohli chtít dostat.</p>
                    </div>
                </div>

                <div class="grid gap-4">
                    @foreach($results as $result)
                        <a href="{{ $result->url }}" class="group bg-white rounded-xl p-6 shadow-sm border border-slate-100 hover:border-primary hover:shadow-md transition-all flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-slate-50 group-hover:bg-primary/10 rounded-lg flex items-center justify-center text-slate-400 group-hover:text-primary transition-colors">
                                    <i class="fa-light fa-location-dot text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 group-hover:text-primary transition-colors">{{ $result->title }}</h4>
                                    <p class="text-sm text-slate-500 line-clamp-1">{{ $result->snippet }}</p>
                                </div>
                            </div>
                            <i class="fa-light fa-chevron-right text-slate-300 group-hover:text-primary transition-all translate-x-0 group-hover:translate-x-1"></i>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-12 p-8 bg-slate-900 rounded-2xl text-white relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="text-xl font-black uppercase tracking-tight mb-2">Nenašli jste, co jste hledali?</h3>
                <p class="text-white/60 text-sm max-w-xl mb-6">
                    Systém se neustále učí. Pokud máte pocit, že by vyhledávání mělo na váš dotaz reagovat lépe, dejte nám vědět.
                </p>
                <a href="#" class="btn btn-primary">Poslat zpětnou vazbu</a>
            </div>
            <i class="fa-light fa-comment-dots absolute -bottom-4 -right-4 text-9xl text-white/5 -rotate-12"></i>
        </div>
    </div>
</div>
@endsection
