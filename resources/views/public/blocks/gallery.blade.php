<section class="block-gallery py-12">
    <div class="container mx-auto px-4">
        @if($data['title'] ?? null)
            <h2 class="text-3xl font-bold mb-8 text-center">{{ $data['title'] }}</h2>
        @endif
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($data['images'] ?? [] as $image)
                <div class="aspect-square bg-gray-200 rounded-lg overflow-hidden group">
                    <img src="{{ asset('storage/' . $image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                </div>
            @endforeach
        </div>
    </div>
</section>
