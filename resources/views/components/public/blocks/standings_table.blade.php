@php
    $teamId = $data['team_id'] ?? null;
    $seasonId = $data['season_id'] ?? null;
    $limit = $data['limit'] ?? 5;
    $compact = $data['compact'] ?? false;
    $showFilters = $data['show_filters'] ?? false;
@endphp

<section class="section-padding bg-white relative overflow-hidden">
    <div class="container">
        @if(!empty($data['title']) || !empty($data['subtitle']))
            <x-section-heading
                :title="$data['title'] ?? (__('general.standings_title') ?? 'Tabulky soutěží')"
                :subtitle="$data['subtitle'] ?? (__('general.standings_subtitle') ?? 'Jak si vedou naše týmy v aktuální sezóně')"
                align="center"
            />
        @endif

        <div class="max-w-4xl mx-auto">
            @livewire('public-standings-table', [
                'teamId' => $teamId,
                'seasonId' => $seasonId,
                'showFilters' => $showFilters,
                'limit' => $limit,
                'compact' => $compact
            ])

            @if(!empty($data['show_all_link']))
                <div class="mt-12 text-center">
                    <a href="{{ route('public.teams.index') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-secondary text-white font-black uppercase tracking-widest text-xs hover:bg-primary hover:shadow-xl hover:shadow-primary/20 transition-all active:scale-95">
                        {{ __('general.view_all_teams') ?? 'Prohlédnout všechny týmy' }}
                        <i class="fa-light fa-arrow-right"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>
