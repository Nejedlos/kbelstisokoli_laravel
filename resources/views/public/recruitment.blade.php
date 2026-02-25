@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('recruitment.hero_headline')"
        :subtitle="__('recruitment.hero_subheadline')"
        :breadcrumbs="[__('recruitment.title') => null]"
        image="assets/img/recruitment/recruitment-header.jpg"
        alignment="center"
    />

    <div class="section-padding bg-white">
        <div class="container">
            {{-- Koho hledáme --}}
            <div class="max-w-3xl mx-auto text-center mb-20">
                <h2 class="text-3xl font-black uppercase tracking-tighter mb-6">{{ __('recruitment.who_we_look_for') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left mt-10">
                    <div class="p-6 bg-slate-50 rounded-2xl flex items-start gap-4 border border-slate-100">
                        <i class="fa-light fa-basketball fa-2x text-primary"></i>
                        <div>
                            <h5 class="font-bold mb-1">{{ __('recruitment.exp_players_title') }}</h5>
                            <p class="text-slate-600 text-sm">{{ __('recruitment.exp_players_text') }}</p>
                        </div>
                    </div>
                    <div class="p-6 bg-slate-50 rounded-2xl flex items-start gap-4 border border-slate-100">
                        <i class="fa-light fa-rotate-left fa-2x text-primary"></i>
                        <div>
                            <h5 class="font-bold mb-1">{{ __('recruitment.return_title') }}</h5>
                            <p class="text-slate-600 text-sm">{{ __('recruitment.return_text') }}</p>
                        </div>
                    </div>
                    <div class="p-6 bg-slate-50 rounded-2xl flex items-start gap-4 border border-slate-100">
                        <i class="fa-light fa-user-group fa-2x text-primary"></i>
                        <div>
                            <h5 class="font-bold mb-1">{{ __('recruitment.team_spirit_title') }}</h5>
                            <p class="text-slate-600 text-sm">{{ __('recruitment.team_spirit_text') }}</p>
                        </div>
                    </div>
                    <div class="p-6 bg-slate-50 rounded-2xl flex items-start gap-4 border border-slate-100">
                        <i class="fa-light fa-trophy fa-2x text-primary"></i>
                        <div>
                            <h5 class="font-bold mb-1">{{ __('recruitment.desire_title') }}</h5>
                            <p class="text-slate-600 text-sm">{{ __('recruitment.desire_text') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Výběr týmu --}}
            <div class="mb-20">
                <h2 class="text-3xl font-black uppercase tracking-tighter text-center mb-10">{{ __('recruitment.which_team_title') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    @foreach($teams ?? [] as $team)
                        <div class="card card-hover border-t-4 {{ $loop->index % 2 == 0 ? 'border-t-primary' : 'border-t-secondary' }} p-8 flex flex-col items-center text-center">
                            <h3 class="text-4xl font-black uppercase tracking-tighter mb-2">{{ $team->name }}</h3>
                            <p class="text-slate-600 mb-8 flex-1">
                                {{ $team->description }}
                            </p>
                            <a href="{{ route('public.teams.show', $team->slug) }}" class="btn {{ $loop->index % 2 == 0 ? 'btn-primary' : 'btn-secondary' }} w-full">{{ __('teams.view_detail') }}</a>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Jak probíhá kontakt --}}
            <div class="bg-slate-50 rounded-[2.5rem] p-8 md:p-16 mb-20 border border-slate-100">
                <div class="max-w-3xl mx-auto">
                    <h2 class="text-3xl font-black uppercase tracking-tighter text-center mb-12">{{ __('recruitment.steps_title') }}</h2>
                    <div class="space-y-12">
                        <div class="flex flex-col md:flex-row gap-6 items-center md:items-start text-center md:text-left">
                            <div class="w-16 h-16 rounded-2xl bg-primary text-white flex items-center justify-center text-2xl font-black flex-shrink-0">1</div>
                            <div>
                                <h4 class="text-xl font-bold mb-2">{{ __('recruitment.step_1_title') }}</h4>
                                <p class="text-slate-600 leading-relaxed">{{ __('recruitment.step_1_text') }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col md:flex-row gap-6 items-center md:items-start text-center md:text-left">
                            <div class="w-16 h-16 rounded-2xl bg-primary text-white flex items-center justify-center text-2xl font-black flex-shrink-0">2</div>
                            <div>
                                <h4 class="text-xl font-bold mb-2">{{ __('recruitment.step_2_title') }}</h4>
                                <p class="text-slate-600 leading-relaxed">{{ __('recruitment.step_2_text') }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col md:flex-row gap-6 items-center md:items-start text-center md:text-left">
                            <div class="w-16 h-16 rounded-2xl bg-primary text-white flex items-center justify-center text-2xl font-black flex-shrink-0">3</div>
                            <div>
                                <h4 class="text-xl font-bold mb-2">{{ __('recruitment.step_3_title') }}</h4>
                                <p class="text-slate-600 leading-relaxed">{{ __('recruitment.step_3_text') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Co si vzít s sebou --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mb-20">
                <div>
                    <h2 class="text-3xl font-black uppercase tracking-tighter mb-6">{{ __('recruitment.what_to_bring') }}</h2>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <i class="fa-light fa-shoe-prints text-primary mt-1"></i>
                            <span class="text-slate-600 font-bold leading-tight uppercase text-xs tracking-wider">{{ __('recruitment.shoes') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-light fa-shirt text-primary mt-1"></i>
                            <span class="text-slate-600 font-bold leading-tight uppercase text-xs tracking-wider">{{ __('recruitment.clothing') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-light fa-bottle-water text-primary mt-1"></i>
                            <span class="text-slate-600 font-bold leading-tight uppercase text-xs tracking-wider">{{ __('recruitment.water') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-light fa-fire text-primary mt-1"></i>
                            <span class="text-slate-600 font-bold leading-tight uppercase text-xs tracking-wider">{{ __('recruitment.mood') }}</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-bg rounded-[2rem] p-4 relative aspect-video flex items-center justify-center overflow-hidden">
                    <x-picture
                        src="assets/img/recruitment/recruitment-content.jpg"
                        alt="Basketbalové hřiště"
                        class="w-full h-full object-cover rounded-[1.5rem] opacity-80"
                    />
                    <div class="absolute inset-0 flex items-center justify-center">
                         <div class="w-20 h-20 rounded-full bg-primary/20 flex items-center justify-center animate-ping absolute"></div>
                         <i class="fa-light fa-basketball fa-4x text-white relative z-10 drop-shadow-xl"></i>
                    </div>
                </div>
            </div>

            {{-- Kontakt / Formulář --}}
            <div id="kontakt" class="max-w-2xl mx-auto">
                <div class="bg-secondary p-8 md:p-12 rounded-[2rem] text-white text-center shadow-xl shadow-secondary/20">
                    <h3 class="text-2xl md:text-3xl font-black uppercase tracking-tighter mb-4">{{ __('recruitment.cta_contact_us') }}</h3>
                    <p class="text-slate-300 mb-10">{{ __('recruitment.cta_text') }}</p>

                    <div class="flex flex-col gap-4">
                        <a href="{{ route('public.contact.index') }}" class="btn btn-primary btn-lg w-full">{{ __('recruitment.contact_form') }}</a>
                        <p class="text-xs text-white/40 mt-4 italic">{{ __('recruitment.form_prep') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mládež --}}
    <div class="bg-bg py-24 border-t border-slate-100 relative overflow-hidden">
        <div class="absolute top-0 right-0 p-12 opacity-[0.03] pointer-events-none">
            <i class="fa-light fa-basketball fa-[15rem] rotate-12"></i>
        </div>

        <div class="container relative z-10">
            <div class="max-w-4xl mx-auto bg-white rounded-[3rem] p-8 md:p-16 shadow-xl shadow-slate-200/60 border border-slate-100 text-center">
                <div class="w-20 h-20 bg-primary/10 rounded-3xl flex items-center justify-center mx-auto mb-8">
                    <i class="fa-light fa-children fa-3x text-primary"></i>
                </div>

                <h3 class="text-3xl md:text-4xl font-black uppercase tracking-tighter mb-6">{{ __('recruitment.youth_recruitment') }}</h3>
                <p class="text-lg text-slate-500 mb-10 max-w-2xl mx-auto leading-relaxed">{{ __('recruitment.youth_recruitment_text') }}</p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ $branding['recruitment_url'] }}"
                       target="_blank"
                       rel="noopener"
                       class="btn btn-primary btn-lg px-12 group"
                       data-track-click="external_link"
                       data-track-label="Youth Recruitment - Bottom"
                       data-track-category="external">
                        <span>{{ __('recruitment.youth_recruitment_cta') }}</span>
                        <i class="fa-light fa-arrow-up-right ml-2 group-hover:-translate-y-0.5 group-hover:translate-x-0.5 transition-transform"></i>
                    </a>
                    <a href="{{ $branding['main_club_url'] }}"
                       target="_blank"
                       rel="noopener"
                       class="btn btn-outline-primary btn-lg px-12 group"
                       data-track-click="external_link"
                       data-track-label="Club Website - Bottom"
                       data-track-category="external">
                        <span>{{ __('recruitment.club_web') }}</span>
                        <i class="fa-light fa-arrow-up-right ml-2 group-hover:-translate-y-0.5 group-hover:translate-x-0.5 transition-transform opacity-70"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
