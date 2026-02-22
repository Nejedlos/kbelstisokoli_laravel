<section class="block-cta py-16 bg-red-50">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-6 text-gray-900">{{ $data['title'] ?? '' }}</h2>
        <a href="{{ $data['button_url'] ?? '#' }}" class="inline-block bg-red-600 text-white px-10 py-4 rounded-full text-xl font-bold shadow-lg hover:scale-105 transition">
            {{ $data['button_text'] ?? '' }}
        </a>
    </div>
</section>
