@extends('layouts.public')

@section('content')
    <x-page-header
        title="Napište nám"
        subtitle="Váš vzkaz doručíme správné osobě"
        :breadcrumbs="['Kontakt' => route('public.contact.index'), 'Napište nám' => null]"
    />

    <div class="section-padding bg-bg relative overflow-hidden">
        {{-- Sportovní dekorace na pozadí --}}
        <div class="absolute top-0 right-0 w-1/3 h-full pointer-events-none opacity-[0.03] overflow-hidden">
            <i class="fa-light fa-basketball text-[30rem] translate-x-1/3 -translate-y-1/4 rotate-12"></i>
        </div>
        <div class="absolute bottom-0 left-0 w-1/4 h-full pointer-events-none opacity-[0.02] overflow-hidden">
            <i class="fa-light fa-envelope-open-text text-[20rem] -translate-x-1/4 translate-y-1/4 -rotate-12"></i>
        </div>

        <div class="container relative z-10">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/60 p-6 md:p-12 border border-slate-100 relative overflow-hidden">
                    {{-- Horní linka --}}
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-primary to-secondary opacity-80"></div>

                    <div class="mb-10 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary/10 text-primary mb-6 ring-8 ring-primary/5">
                            <i class="fa-light fa-comments-question text-3xl"></i>
                        </div>
                        <h2 class="text-3xl font-black uppercase tracking-tighter text-secondary mb-3">Kontaktní formulář</h2>
                        <p class="text-slate-500 text-sm leading-relaxed max-w-sm mx-auto">
                            Vyplňte prosím níže uvedené údaje a my se vám ozveme co nejdříve to bude možné.
                        </p>
                    </div>

                    <livewire:contact-form :to="$to" />
                </div>

                <div class="mt-16 text-center">
                    <div class="inline-flex items-center gap-2 px-6 py-3 bg-white rounded-full border border-slate-100 shadow-sm text-sm text-slate-500">
                        <span>Potřebujete poradit s něčím jiným?</span>
                        <a href="{{ route('public.contact.index') }}" class="text-primary font-black uppercase tracking-widest text-[11px] hover:text-secondary transition-colors ml-2">
                            Zpět na kontakty
                            <i class="fa-light fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
