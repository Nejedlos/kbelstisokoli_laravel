<section class="block-stats-cards py-12 bg-gray-900 text-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            @foreach($data['stats'] ?? [] as $stat)
                <div class="text-center">
                    <div class="text-4xl font-black text-red-600 mb-2">{{ $stat['value'] }}</div>
                    <div class="text-sm uppercase tracking-wider text-gray-400">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
