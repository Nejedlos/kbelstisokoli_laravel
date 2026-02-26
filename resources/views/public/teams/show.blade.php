@extends('layouts.public')

@section('content')
    <x-page-header
        :title="$team->name"
        :subtitle="match($team->slug) {
            'muzi-a' => 'Vlajková loď klubu (2. liga skupina A)',
            'muzi-b' => 'Zkušený tým (Pražský přebor A)',
            'muzi-c' => 'Soutěžní tým s ambicemi (Pražský přebor B)',
            'muzi-d' => 'Soutěžní basket (1. třída)',
            'muzi-e' => 'Tým se skvělou partou (3. třída B)',
            default => $team->name
        }"
        :breadcrumbs="[__('teams.breadcrumbs') => route('public.teams.index'), $team->name => null]"
        :image="'assets/img/teams/' . $team->slug . '-header.jpg'"
        alignment="left"
    />

    <div class="section-padding bg-white">
        <div class="container">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                {{-- Levý sloupec - O týmu --}}
                <div class="lg:col-span-2">
                    <h2 class="text-3xl font-black uppercase tracking-tighter mb-6">{{ __('teams.detail.about') }}</h2>
                    <div class="prose prose-lg text-slate-600 max-w-none mb-12">
                        <p>{{ $team->description }}</p>
                        <p>{{ __('teams.detail.' . str_replace('-', '_', $team->slug) . '_about') }}</p>
                    </div>

                    <h3 class="text-2xl font-black uppercase tracking-tighter mb-6">{{ __('teams.detail.suitable_for') }}</h3>
                    <ul class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-12">
                        @foreach(__('teams.detail.' . str_replace('-', '_', $team->slug) . '_suitable') as $item)
                            <li class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl">
                                <i class="fa-light fa-check-circle text-primary"></i>
                                <span class="text-sm font-bold text-secondary">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <h3 class="text-2xl font-black uppercase tracking-tighter mb-6">{{ __('teams.detail.how_to_join') }}</h3>
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-black flex-shrink-0">1</div>
                            <div>
                                <h5 class="font-bold mb-1">{{ __('teams.detail.step_1_title') }}</h5>
                                <p class="text-slate-600 text-sm">{{ __('teams.detail.step_1_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-black flex-shrink-0">2</div>
                            <div>
                                <h5 class="font-bold mb-1">{{ __('teams.detail.step_2_title') }}</h5>
                                <p class="text-slate-600 text-sm">{{ __('teams.detail.step_2_desc') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-black flex-shrink-0">3</div>
                            <div>
                                <h5 class="font-bold mb-1">{{ __('teams.detail.step_3_title') }}</h5>
                                <p class="text-slate-600 text-sm">{{ __('teams.detail.step_3_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pravý sloupec - Info panel --}}
                <div>
                    <div class="bg-slate-50 rounded-3xl p-8 sticky top-24">
                        <h4 class="text-xl font-black uppercase tracking-tighter mb-6">{{ __('teams.detail.info') }}</h4>

                        <div class="space-y-6 mb-8">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                                    <i class="fa-light fa-trophy text-primary"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('teams.detail.competition') }}</span>
                                    <span class="font-bold text-secondary">
                                        {{ match($team->slug) {
                                            'muzi-a' => '2. liga (skupina A)',
                                            'muzi-b' => 'Pražský přebor A',
                                            'muzi-c' => 'Pražský přebor B',
                                            'muzi-d' => '1. třída',
                                            'muzi-e' => '3. třída B',
                                            default => ''
                                        } }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                                    <i class="fa-light fa-user-plus text-primary"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('teams.detail.recruitment_status') }}</span>
                                    <span class="badge badge-success uppercase tracking-widest text-[10px]">{{ app()->getLocale() === 'cs' ? 'Otevřen' : 'Open' }}</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                                    <i class="fa-light fa-calendar-clock text-primary"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('teams.detail.training_time') }}</span>
                                    <span class="font-bold text-secondary">{{ app()->getLocale() === 'cs' ? 'Dle rozpisu v hale' : 'According to gym schedule' }}</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center flex-shrink-0">
                                    <i class="fa-light fa-envelope text-primary"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5">{{ __('teams.detail.contact') }}</span>
                                    <a href="{{ route('public.recruitment.index') }}" class="font-bold text-primary hover:underline">{{ app()->getLocale() === 'cs' ? 'Náborový kontakt' : 'Recruitment contact' }}</a>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <a href="{{ route('public.recruitment.index') }}" class="btn btn-primary w-full">
                                <i class="fa-light fa-paper-plane mr-2"></i> {{ __('teams.detail.cta_join') }}
                            </a>
                            <a href="{{ route('public.matches.index') }}" class="btn btn-outline w-full border-slate-200 hover:border-primary">
                                <i class="fa-light fa-calendar-days mr-2"></i> {{ __('teams.detail.cta_matches') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Galerie --}}
    <div class="section-padding bg-bg overflow-hidden">
        <div class="container text-center">
            <x-section-heading
                title="{{ __('teams.detail.gallery') }}"
                :subtitle="app()->getLocale() === 'cs' ? 'Nahlédněte do života našeho týmu na hřišti i mimo něj.' : 'Take a look at the life of our team on and off the court.'"
                alignment="center"
            />

            @if($randomPhotos->isNotEmpty())
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($randomPhotos as $photo)
                        <div class="group relative aspect-square overflow-hidden rounded-2xl bg-slate-200">
                            <img
                                src="{{ $photo->getUrl('optimized') }}"
                                alt="{{ $photo->alt_text ?: $photo->title }}"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                loading="lazy"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex items-end p-4">
                                <p class="text-white text-xs font-bold text-left line-clamp-2">
                                    {{ $photo->title }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-12">
                    <a href="{{ route('public.galleries.index') }}" class="btn btn-outline border-slate-200 hover:border-primary">
                        <i class="fa-light fa-images mr-2"></i> {{ app()->getLocale() === 'cs' ? 'Zobrazit všechny galerie' : 'View all galleries' }}
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 opacity-40 grayscale group hover:grayscale-0 transition-all duration-500">
                    <div class="aspect-square bg-slate-200 rounded-2xl animate-pulse"></div>
                    <div class="aspect-square bg-slate-200 rounded-2xl animate-pulse delay-75"></div>
                    <div class="aspect-square bg-slate-200 rounded-2xl animate-pulse delay-150"></div>
                    <div class="aspect-square bg-slate-200 rounded-2xl animate-pulse delay-300"></div>
                </div>
                <p class="mt-8 text-slate-400 italic">{{ __('teams.detail.no_data') }}</p>
            @endif
        </div>
    </div>

    {{-- CTA --}}
    <div class="bg-primary py-16">
        <div class="container">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8 text-white text-center md:text-left">
                <div>
                    <h2 class="text-3xl font-black uppercase tracking-tighter mb-2">{{ app()->getLocale() === 'cs' ? 'Chceš se stát součástí týmu' : 'Want to become part of the team' }} {{ $team->name }}?</h2>
                    <p class="text-white/80">{{ app()->getLocale() === 'cs' ? 'Ozvěte se nám a domluvíme se na prvním tréninku.' : 'Contact us and we will arrange the first training.' }}</p>
                </div>
                <a href="{{ route('public.recruitment.index') }}" class="btn bg-white text-primary hover:bg-secondary hover:text-white btn-lg">
                    {{ app()->getLocale() === 'cs' ? 'Chci se přidat' : 'I want to join' }}
                </a>
            </div>
        </div>
    </div>
@endsection
