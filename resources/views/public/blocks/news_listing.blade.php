<section class="block-news-listing py-12">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8">{{ $data['title'] ?? 'Aktuality' }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <p class="text-gray-500 italic col-span-full">Placeholder pro výpis novinek (limit: {{ $data['limit'] ?? 3 }})</p>
        </div>
    </div>
</section>
