<section class="block-cards-grid py-12">
    <div class="container mx-auto px-4">
        @if($data['title'] ?? null)
            <h2 class="text-3xl font-bold mb-8 text-center">{{ $data['title'] }}</h2>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($data['cards'] ?? [] as $card)
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 hover:shadow-lg transition">
                    @if($card['icon'] ?? null)
                        <img src="{{ asset('storage/' . $card['icon']) }}" class="w-12 h-12 mb-4">
                    @endif
                    <h3 class="text-xl font-bold mb-2">{{ $card['title'] }}</h3>
                    <p class="text-gray-600 mb-4">{{ $card['description'] ?? '' }}</p>
                    @if($card['link'] ?? null)
                        <a href="{{ $card['link'] }}" class="text-red-600 font-bold hover:underline">Více &rarr;</a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
