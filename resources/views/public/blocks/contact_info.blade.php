<section class="block-contact-info py-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div>
                <h2 class="text-3xl font-bold mb-6">{{ $data['title'] }}</h2>
                <div class="space-y-4">
                    @if($data['address'] ?? null)
                        <p><strong>Adresa:</strong><br>{{ $data['address'] }}</p>
                    @endif
                    @if($data['email'] ?? null)
                        <p><strong>Email:</strong> <a href="mailto:{{ $data['email'] }}" class="text-red-600 hover:underline">{{ $data['email'] }}</a></p>
                    @endif
                    @if($data['phone'] ?? null)
                        <p><strong>Telefon:</strong> {{ $data['phone'] }}</p>
                    @endif
                </div>
            </div>
            @if($data['show_map'] ?? false)
                <div class="bg-gray-200 rounded-xl flex items-center justify-center text-gray-500 italic h-64 shadow-inner">
                    Mapa (placeholder)
                </div>
            @endif
        </div>
    </div>
</section>
