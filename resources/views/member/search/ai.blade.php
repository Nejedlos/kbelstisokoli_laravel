@extends('layouts.member')

@section('content')
<div class="container-fluid px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <header class="mb-8">
            <h1 class="text-3xl font-black uppercase tracking-tight text-secondary">
                {{ __('AI vyhledávání') }}
            </h1>
            <p class="text-slate-500 mt-2">
                {{ __('Hledaný výraz') }}: <span class="font-bold text-slate-900">"{{ $query }}"</span>
            </p>
        </header>

        <div class="space-y-6">
            @if(empty($query))
                <div class="bg-white rounded-xl shadow-sm p-12 text-center border border-slate-100">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fa-light fa-sparkles text-3xl text-slate-300"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 mb-2">{{ __('Zadejte dotaz pro AI') }}</h2>
                    <p class="text-slate-500 max-w-md mx-auto">
                        {{ __('Použijte vyhledávací pole v horní liště nebo tlačítko s ikonou AI u globálního vyhledávání.') }}
                    </p>
                </div>
            @else
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 bg-primary/5 border-b border-primary/10 flex items-center gap-3">
                        <div class="w-9 h-9 bg-primary text-white rounded-lg flex items-center justify-center shrink-0">
                            <i class="fa-light fa-sparkles"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-widest text-primary">{{ __('Odpověď AI asistenta') }}</h3>
                            <p class="text-xs text-slate-600">{{ __('Odpověď je generována na základě lokálního kontextu (členská a admin sekce).') }}</p>
                        </div>
                    </div>
                    <div class="p-6 prose prose-sm max-w-none text-slate-800">
                        @if($answer)
                            <div class="whitespace-pre-wrap font-sans text-[13px] leading-relaxed">{!! Str::markdown($answer) !!}</div>
                        @else
                            <p class="text-slate-500">{{ __('Nepodařilo se vygenerovat odpověď. Zkuste dotaz přeformulovat.') }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-6 bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <div class="text-[10px] text-slate-500 font-black uppercase tracking-widest flex items-center gap-2 mb-2">
                        <span class="w-4 h-px bg-slate-200"></span>
                        {{ __('Použitý kontext (zdroje)') }}
                        <span class="flex-1 h-px bg-slate-200"></span>
                    </div>

                    @if($sources->isEmpty())
                        <p class="text-slate-500 text-sm">{{ __('Žádné relevantní zdroje nebyly nalezeny v lokálním indexu.') }}</p>
                    @else
                        <ul class="grid gap-2">
                            @foreach($sources as $doc)
                                <li class="flex items-start gap-3 bg-white rounded-lg p-3 border border-slate-100">
                                    <div class="w-7 h-7 rounded bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                                        <i class="fa-light fa-file-lines text-[13px]"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[12px] font-bold text-slate-800">
                                            {{ $doc->title }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 uppercase font-black">
                                            {{ $doc->type }}
                                        </div>
                                        @if($doc->url)
                                            <a href="{{ $doc->url }}" class="mt-1 inline-flex items-center text-[10px] text-primary font-bold hover:underline">
                                                {{ __('Přejít na stránku') }} <i class="fa-light fa-arrow-up-right-from-square ml-1 text-[8px]"></i>
                                            </a>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
        </div>

        <div class="mt-12 p-8 bg-slate-900 rounded-2xl text-white relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="text-xl font-black uppercase tracking-tight mb-2">{{ __('Nenašli jste, co jste hledali?') }}</h3>
                <p class="text-white/60 text-sm max-w-xl mb-6">
                    {{ __('Systém se neustále učí. Pokud máte pocit, že by vyhledávání mělo na váš dotaz reagovat lépe, dejte nám vědět.') }}
                </p>
                <a href="#" class="btn btn-primary">{{ __('Poslat zpětnou vazbu') }}</a>
            </div>
            <i class="fa-light fa-comment-dots absolute -bottom-4 -right-4 text-9xl text-white/5 -rotate-12"></i>
        </div>
    </div>
</div>
@endsection
