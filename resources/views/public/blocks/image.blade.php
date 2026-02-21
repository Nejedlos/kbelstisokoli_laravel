<section class="block-image py-12">
    <div class="container mx-auto px-4">
        <figure class="overflow-hidden rounded-xl shadow-lg">
            <img src="{{ asset('storage/' . $data['image']) }}" alt="{{ $data['alt'] ?? '' }}" class="w-full">
            @if($data['caption'] ?? null)
                <figcaption class="p-4 bg-gray-50 text-center italic text-gray-600">
                    {{ $data['caption'] }}
                </figcaption>
            @endif
        </figure>
    </div>
</section>
