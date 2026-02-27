@extends('layouts.public')

@section('content')
    @php
        $statusColors = [
            'planned' => 'bg-accent text-white',
            'scheduled' => 'bg-accent text-white',
            'completed' => 'bg-success text-white',
            'played' => 'bg-success text-white',
            'cancelled' => 'bg-danger text-white',
            'postponed' => 'bg-warning text-black',
        ];
        $statusLabels = [
            'planned' => __('matches.planned'),
            'scheduled' => __('matches.planned'),
            'completed' => __('matches.completed'),
            'played' => __('matches.completed'),
            'cancelled' => __('matches.cancelled'),
            'postponed' => __('matches.postponed'),
        ];

        $hasKlub = $match->teams->contains('slug', 'klub');
        $teamNames = $match->teams->pluck('name')->join(' & ');
        $mainTeamName = ($hasKlub || $match->teams->count() > 1) ? ($hasKlub ? 'Sokoli (Celý klub)' : $teamNames) : ($match->teams->first()?->name ?? $match->team?->name);
    @endphp
    <x-page-header
        :title="$mainTeamName . ' ' . __('matches.vs') . ' ' . $match->opponent->name"
        :subtitle="$match->scheduled_at->format('d. m. Y H:i') . ' | ' . ($match->location ?? __('matches.location_not_specified'))"
        :breadcrumbs="[__('matches.breadcrumbs') => route('public.matches.index'), __('matches.view_detail') => null]"
    />

    <div class="section-padding bg-bg">
        <div class="container max-w-5xl">
            <!-- Match Center Hero -->
            <div class="card overflow-hidden mb-12 border-t-8 border-t-primary">
                <div class="bg-secondary text-white p-8 md:p-16">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-12">
                        <!-- Home Team -->
                        <div class="flex-1 flex flex-col items-center text-center">
                            <div class="w-24 h-24 md:w-32 md:h-24 bg-white/10 rounded-club flex items-center justify-center mb-6 border border-white/20">
                                @if($match->is_home && ($branding['logo_path'] ?? null))
                                    <img src="{{ asset('storage/' . $branding['logo_path']) }}" class="max-w-[80%] max-h-[80%] object-contain" alt="">
                                @else
                                    <span class="text-4xl font-black opacity-20">LOGO</span>
                                @endif
                            </div>
                            <h3 class="text-2xl md:text-3xl font-black uppercase tracking-tight">
                                {{ $match->is_home ? ($branding['club_name'] ?? 'Sokoli') : $match->opponent->name }}
                            </h3>
                        </div>

                        <div class="flex flex-col items-center min-w-[150px]">
                            @if(in_array($match->status, ['completed', 'played']) && ($match->score_home !== null || $match->score_away !== null))
                                <div class="text-6xl md:text-8xl font-black tabular-nums tracking-tighter leading-none mb-4">
                                    {{ $match->score_home ?? 0 }}<span class="text-primary">:</span>{{ $match->score_away ?? 0 }}
                                </div>
                            @else
                                <div class="text-4xl md:text-5xl font-black opacity-30 mb-4 uppercase tracking-widest italic">VS</div>
                            @endif
                            <span class="px-4 py-1 rounded-full text-xs font-black uppercase tracking-widest {{ $statusColors[$match->status] ?? 'bg-slate-700' }}">
                                {{ $statusLabels[$match->status] ?? $match->status }}
                            </span>
                            @if(in_array($match->status, ['completed', 'played']) && $match->score_home === null && $match->score_away === null)
                                <div class="mt-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">
                                    {{ __('matches.result_missing') }}
                                </div>
                            @endif
                        </div>

                        <!-- Away Team -->
                        <div class="flex-1 flex flex-col items-center text-center">
                            <div class="w-24 h-24 md:w-32 md:h-24 bg-white/10 rounded-club flex items-center justify-center mb-6 border border-white/20">
                                @if(!$match->is_home && ($branding['logo_path'] ?? null))
                                    <img src="{{ asset('storage/' . $branding['logo_path']) }}" class="max-w-[80%] max-h-[80%] object-contain" alt="">
                                @else
                                    <span class="text-4xl font-black opacity-20">LOGO</span>
                                @endif
                            </div>
                            <h3 class="text-2xl md:text-3xl font-black uppercase tracking-tight">
                                {{ $match->is_home ? $match->opponent->name : ($branding['club_name'] ?? 'Sokoli') }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Match Meta Info -->
                <div class="bg-white border-t border-slate-100 p-6 flex flex-wrap items-center justify-center gap-x-12 gap-y-4 text-sm font-bold uppercase tracking-widest text-slate-500">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ $match->scheduled_at->format('d. m. Y') }}
                    </div>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $match->scheduled_at->format('H:i') }}
                    </div>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ $match->location ?? 'Místo neuvedeno' }}
                    </div>
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        {{ $match->season->name }}
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    @if($match->notes_public)
                        <section class="card p-8">
                            <h2 class="text-2xl font-black uppercase tracking-tight mb-6 border-b border-slate-100 pb-4">
                                {{ app()->getLocale() === 'cs' ? 'Informace k zápasu' : 'Match information' }}
                            </h2>
                            <div class="prose prose-slate max-w-none prose-headings:font-display prose-headings:uppercase prose-headings:tracking-tight prose-a:text-primary">
                                {!! nl2br(e($match->notes_public)) !!}
                            </div>
                        </section>
                    @endif

                    <section class="card p-8">
                        <h2 class="text-2xl font-black uppercase tracking-tight mb-6 border-b border-slate-100 pb-4">
                            {{ app()->getLocale() === 'cs' ? 'Reportáž ze zápasu' : 'Match report' }}
                        </h2>
                        <x-empty-state
                            :title="app()->getLocale() === 'cs' ? 'Reportáž připravujeme' : 'Report in preparation'"
                            :subtitle="app()->getLocale() === 'cs' ? 'Podrobné statistiky a komentář k zápasu budou doplněny co nejdříve po jeho skončení.' : 'Detailed statistics and match commentary will be added as soon as possible after the game.'"
                        />
                    </section>
                </div>

                <!-- Sidebar -->
                <div class="space-y-8">
                    <!-- Additional Info Widget -->
                    <aside class="card p-6 bg-secondary text-white">
                        <h3 class="text-lg font-black uppercase tracking-tight mb-4 text-primary">{{ app()->getLocale() === 'cs' ? 'Důležité info' : 'Important info' }}</h3>
                        <ul class="space-y-4 text-sm font-medium">
                            <li class="flex justify-between border-b border-white/10 pb-2">
                                <span class="opacity-60">{{ app()->getLocale() === 'cs' ? 'Sraz týmu:' : 'Team meeting:' }}</span>
                                <span>{{ $match->scheduled_at->subMinutes(60)->format('H:i') }}</span>
                            </li>
                            <li class="flex justify-between border-b border-white/10 pb-2">
                                <span class="opacity-60">{{ app()->getLocale() === 'cs' ? 'Dresy:' : 'Jerseys:' }}</span>
                                <span>{{ $match->is_home ? (app()->getLocale() === 'cs' ? 'Bílá (Světlá)' : 'White (Light)') : (app()->getLocale() === 'cs' ? 'Tmavá' : 'Dark') }}</span>
                            </li>
                        </ul>
                    </aside>

                    <!-- Back Link -->
                    <a href="{{ route('public.matches.index') }}" class="btn btn-outline-primary w-full py-4 uppercase tracking-widest font-black text-sm">
                        &larr; {{ app()->getLocale() === 'cs' ? 'Zpět na seznam zápasů' : 'Back to match list' }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Placeholder pro budoucí interaktivitu (např. auto-refresh skóre ze svazu)
</script>
@endpush
