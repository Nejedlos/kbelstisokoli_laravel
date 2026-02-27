@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('trainings.title')"
        :subtitle="__('trainings.subtitle')"
        :breadcrumbs="[__('trainings.breadcrumbs') => null]"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            @if($teams->isEmpty())
                <x-empty-state
                    :title="__('trainings.empty_title')"
                    :subtitle="__('trainings.empty_subtitle')"
                />
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-20">
                    @foreach($teams as $team)
                        <div class="card p-8 border-t-4 {{ $loop->first ? 'border-primary' : 'border-secondary' }}">
                            <h2 class="text-3xl font-black uppercase tracking-tighter mb-6 text-secondary">{{ $team->name }}</h2>
                            <div class="mb-8 text-slate-600 leading-relaxed">
                                {{ $team->description ?? __('trainings.no_description') }}
                            </div>

                            <h3 class="text-sm font-black uppercase tracking-widest text-primary mb-4">{{ __('trainings.upcoming') }}</h3>
                            @if($team->trainings->isEmpty())
                                <p class="text-sm text-slate-400 italic bg-slate-50 p-4 rounded-xl border border-slate-100">{{ __('trainings.no_trainings') }}</p>
                            @else
                                <div class="space-y-4">
                                    @foreach($team->trainings as $training)
                                        <div class="flex items-center gap-6 p-4 bg-slate-50 rounded-2xl border border-slate-100 group hover:bg-white hover:shadow-md transition-all">
                                            <div class="bg-white shadow-sm text-secondary p-3 rounded-xl text-center min-w-[70px] border border-slate-100 group-hover:bg-primary group-hover:text-white transition-colors">
                                                <div class="text-[10px] font-black uppercase tracking-widest opacity-60">{{ $training->starts_at->translatedFormat('M') }}</div>
                                                <div class="text-2xl font-black leading-none">{{ $training->starts_at->format('d') }}</div>
                                            </div>
                                            <div>
                                                <div class="font-black text-secondary uppercase tracking-tight">
                                                    {{ $training->starts_at->format('H:i') }} - {{ $training->ends_at->format('H:i') }}
                                                </div>
                                                <div class="text-sm text-slate-500 flex items-center mt-1">
                                                    <i class="fa-light fa-location-dot mr-2 text-primary opacity-70"></i>
                                                    {{ $training->location ?? __('trainings.location_not_specified') }}
                                                </div>

                                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                                    <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-white border border-slate-100 text-[10px] font-black uppercase tracking-widest text-slate-400 shadow-sm" title="{{ __('trainings.expected') }}">
                                                        <i class="fa-light fa-users-viewfinder text-primary/50"></i>
                                                        {{ $training->total_expected_count ?? 0 }}
                                                    </div>
                                                    <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-emerald-50 border border-emerald-100 text-[10px] font-black uppercase tracking-widest text-emerald-600 shadow-sm" title="{{ __('trainings.confirmed') }}">
                                                        <i class="fa-light fa-circle-check"></i>
                                                        {{ $training->confirmed_count ?? 0 }}
                                                    </div>
                                                    <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-rose-50 border border-rose-100 text-[10px] font-black uppercase tracking-widest text-rose-600 shadow-sm" title="{{ __('trainings.apologies') }}">
                                                        <i class="fa-light fa-circle-xmark"></i>
                                                        {{ $training->declined_count ?? 0 }}
                                                    </div>
                                                    <a href="{{ route('member.attendance.show', ['type' => 'training', 'id' => $training->id]) }}"
                                                       class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-primary/10 border border-primary/20 text-[10px] font-black uppercase tracking-widest text-primary hover:bg-primary hover:text-white transition-all shadow-sm ml-auto">
                                                        {{ __('trainings.detail_in_member') }}
                                                        <i class="fa-light fa-arrow-right"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Practical Info & Map --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <div class="lg:col-span-1 space-y-8">
                    <div>
                        <h2 class="text-3xl font-black uppercase tracking-tighter text-secondary mb-6">{{ __('trainings.practical_info') }}</h2>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-4 p-4 bg-white rounded-2xl border border-slate-100">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                    <i class="fa-light fa-shoe-prints"></i>
                                </div>
                                <span class="text-sm font-bold text-secondary uppercase tracking-tight mt-2">{{ __('trainings.shoes') }}</span>
                            </li>
                            <li class="flex items-start gap-4 p-4 bg-white rounded-2xl border border-slate-100">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                    <i class="fa-light fa-shirt"></i>
                                </div>
                                <span class="text-sm font-bold text-secondary uppercase tracking-tight mt-2">{{ __('trainings.clothing') }}</span>
                            </li>
                            <li class="flex items-start gap-4 p-4 bg-white rounded-2xl border border-slate-100">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                    <i class="fa-light fa-bottle-water"></i>
                                </div>
                                <span class="text-sm font-bold text-secondary uppercase tracking-tight mt-2">{{ __('trainings.water') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <h2 class="text-3xl font-black uppercase tracking-tighter text-secondary mb-6">{{ __('trainings.where_we_train') }}</h2>
                    <div class="card h-[400px] bg-slate-100 relative overflow-hidden border-2 border-slate-200">
                        @if($branding['venue']['map_url'] ?? null)
                            <iframe src="{{ $branding['venue']['map_url'] }}"
                                    class="absolute inset-0 w-full h-full"
                                    style="border:0;"
                                    allowfullscreen=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    aria-label="{{ app()->getLocale() === 'cs' ? 'Mapa haly' : 'Gym map' }} – {{ $branding['venue']['name'] ?? '' }}">
                            </iframe>
                        @else
                            <div class="flex items-center justify-center h-full text-slate-400 italic">
                                {{ __('contact.map_not_available') ?? 'Mapa není k dispozici' }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
