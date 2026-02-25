@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('history.title')"
        :subtitle="__('history.subtitle')"
        :breadcrumbs="[__('history.breadcrumbs') => null]"
    />

    <div class="section-padding bg-bg relative overflow-hidden">
        {{-- Decor elements --}}
        <div class="absolute top-0 right-0 p-12 opacity-[0.03] pointer-events-none">
            <i class="fa-light fa-hourglass-end fa-[20rem] rotate-12"></i>
        </div>

        <div class="container relative z-10">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-20">
                    <x-section-heading
                        :title="__('history.tradition')"
                        :subtitle="__('history.empty_subtitle')"
                        alignment="center"
                    />
                </div>

                {{-- Timeline layout --}}
                <div class="relative space-y-12 before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">

                    {{-- Timeline Item 1 --}}
                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-slate-200 group-[.is-active]:bg-primary text-slate-500 group-[.is-active]:text-white shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 transition-colors duration-500">
                            <i class="fa-light fa-calendar-star text-sm"></i>
                        </div>
                        <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] card p-6 md:p-8 hover:shadow-xl transition-shadow duration-500">
                            <div class="flex items-center justify-between mb-2">
                                <time class="font-black text-primary uppercase tracking-widest text-sm">Založení oddílu</time>
                            </div>
                            <div class="text-slate-600 leading-relaxed">
                                Basketbal ve Kbelích má hluboké kořeny sahající desítky let do minulosti. Vše začalo jako parta nadšenců v rámci TJ Sokol Kbely.
                            </div>
                        </div>
                    </div>

                    {{-- Timeline Item 2 --}}
                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-slate-200 text-slate-500 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 transition-colors">
                            <i class="fa-light fa-trophy text-sm"></i>
                        </div>
                        <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] card p-6 md:p-8 hover:shadow-xl transition-shadow">
                            <div class="flex items-center justify-between mb-2">
                                <time class="font-black text-secondary uppercase tracking-widest text-sm">Úspěchy v soutěžích</time>
                            </div>
                            <div class="text-slate-600 leading-relaxed">
                                Postupem času se kbelský basketbal vypracoval v respektovanou značku na pražské basketbalové mapě s týmy v různých úrovních Pražského přeboru.
                            </div>
                        </div>
                    </div>

                    {{-- Timeline Item 3 --}}
                    <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-slate-200 text-slate-500 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 transition-colors">
                            <i class="fa-light fa-users-medical text-sm"></i>
                        </div>
                        <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] card p-6 md:p-8 hover:shadow-xl transition-shadow">
                            <div class="flex items-center justify-between mb-2">
                                <time class="font-black text-secondary uppercase tracking-widest text-sm">Současnost: Týmy C a E</time>
                            </div>
                            <div class="text-slate-600 leading-relaxed">
                                Dnes se soustředíme na rozvoj komunity kolem mužských týmů C a E, které propojují zkušené hráče s mladší generací v duchu fair-play a radosti ze hry.
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-24 bg-white rounded-[3rem] p-8 md:p-16 border border-slate-100 shadow-xl shadow-slate-200/50 text-center relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <i class="fa-light fa-quote-right fa-6x"></i>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-black uppercase tracking-tighter text-secondary mb-6">{{ __('history.community') }}</h3>
                    <p class="text-lg text-slate-500 leading-relaxed max-w-2xl mx-auto italic">
                        "Kbelský basketbal není jen o výsledcích na tabuli, ale o lidech, kteří tvoří jednu velkou sportovní rodinu už po generace."
                    </p>
                </div>

                <div class="mt-12 text-center">
                    <a href="{{ route('public.teams.index') }}" class="btn btn-primary btn-lg">
                        {{ __('nav.team') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
