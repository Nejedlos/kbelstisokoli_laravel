<section class="block-cards-grid section-padding">
    <div class="container">
        @if($data['title'] ?? null)
            <x-section-heading :title="$data['title']" :subtitle="($data['subtitle'] ?? null)" align="center" />
        @endif

        @php $cards = $data['cards'] ?? []; @endphp
        @if(empty($cards))
            <x-empty-state title="Žádná data" subtitle="Karty budou doplněny později." />
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($cards as $card)
                    <div class="card card-hover p-6">
                        @if($card['icon'] ?? null)
                            <img src="{{ asset('storage/' . $card['icon']) }}" class="w-12 h-12 mb-4" alt="">
                        @endif
                        <h3 class="text-xl font-bold mb-2">{{ $card['title'] }}</h3>
                        <p class="text-slate-600 mb-4">{{ $card['description'] ?? '' }}</p>
                        @if($card['link'] ?? null)
                            <a href="{{ $card['link'] }}" class="btn btn-outline">Více</a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
