<section class="block-matches-listing py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8">{{ $data['title'] ?? 'Zápasy' }}</h2>
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <p class="p-8 text-gray-500 text-center italic">Placeholder pro výpis zápasů (typ: {{ $data['type'] ?? 'upcoming' }}, limit: {{ $data['limit'] ?? 5 }})</p>
        </div>
    </div>
</section>
