@php
    $listingType = ($data['type'] ?? 'upcoming') === 'latest' ? 'latest' : 'upcoming';
    $matches = app(\App\Services\PublicMatchListingService::class)
        ->forCurrentSeason($listingType, (int) ($data['limit'] ?? 5));
@endphp

<section class="block-matches-listing section-padding bg-slate-50">
    <div class="container">
        <x-section-heading
            :title="($data['title'] ?? __('general.nav.matches'))"
            :subtitle="$listingType === 'upcoming' ? __('matches.upcoming') : __('matches.latest')"
            align="center"
        />

        @if($matches->isEmpty())
            <div class="card overflow-hidden">
                <div class="p-8 text-slate-500 text-center italic">
                    {{ $listingType === 'upcoming' ? __('matches.empty_upcoming') : __('matches.empty_latest') }}
                </div>
            </div>
        @else
            <div class="flex flex-col gap-6 max-w-5xl mx-auto">
                @foreach($matches as $match)
                    <x-match-card :match="$match" />
                @endforeach
            </div>
        @endif

        <div class="mt-10 text-center">
            <a href="{{ route('public.matches.index', ['type' => $listingType]) }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-secondary text-white font-black uppercase tracking-widest text-xs hover:bg-primary hover:shadow-xl hover:shadow-primary/20 transition-all active:scale-95">
                {{ __('general.common.view_all') }}
                <i class="fa-light fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
