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
                        :title="__('history.empty_title')"
                        :subtitle="__('history.empty_subtitle')"
                        align="center"
                    />
                    <div class="max-w-3xl mx-auto mt-8 text-lg text-slate-600 leading-relaxed">
                        {{ __('history.intro') }}
                    </div>
                </div>

                {{-- Timeline layout --}}
                <div class="relative space-y-12 before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent mb-32">

                    @foreach(__('history.milestones') as $year => $milestone)
                        <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group {{ $loop->first ? 'is-active' : '' }}">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-slate-200 group-[.is-active]:bg-primary text-slate-500 group-[.is-active]:text-white shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 transition-colors duration-500">
                                <span class="text-[10px] font-bold">{{ $year === 'today' ? 'NOW' : $year }}</span>
                            </div>
                            <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] card p-6 md:p-8 hover:shadow-xl transition-shadow duration-500">
                                <div class="flex items-center justify-between mb-2">
                                    <time class="font-black text-primary uppercase tracking-widest text-sm">{{ $milestone['title'] }}</time>
                                </div>
                                <div class="text-slate-600 leading-relaxed text-sm">
                                    {{ $milestone['content'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Detailed Sections --}}
                <div class="space-y-24">
                    @foreach(__('history.sections') as $key => $section)
                        <div class="relative">
                            <h3 class="text-3xl font-black uppercase tracking-tighter text-secondary mb-8 flex items-center gap-4">
                                <span class="w-12 h-1px bg-primary block"></span>
                                {{ $section['title'] }}
                            </h3>
                            <div class="grid gap-6 text-slate-600 leading-relaxed">
                                @foreach($section['paragraphs'] as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Current Teams Info --}}
                <div class="mt-24 grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach(__('history.current_teams') as $key => $team)
                        <div class="card p-8 border-t-4 {{ $loop->index % 2 == 0 ? 'border-t-primary' : 'border-t-secondary' }}">
                            <h4 class="text-xl font-bold mb-4">{{ $team['title'] }}</h4>
                            <ul class="space-y-2 text-slate-600 mb-6">
                                <li><strong>{{ $team['competition'] }}</strong></li>
                                @if(isset($team['since']))
                                    <li>{{ $team['since'] }}</li>
                                @endif
                            </ul>
                            <a href="{{ $team['link'] }}" target="_blank" rel="noopener" class="text-sm font-bold text-primary hover:text-secondary transition-colors inline-flex items-center gap-2">
                                {{ __('teams.detail.competition') }} na cz.basketball
                                <i class="fa-light fa-arrow-up-right-from-square text-xs"></i>
                            </a>
                        </div>
                    @endforeach
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
