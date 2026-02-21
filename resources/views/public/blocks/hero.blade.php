<section class="block-hero section-padding bg-secondary text-white text-{{ $data['alignment'] ?? 'center' }} relative overflow-hidden">
    @if($data['image'] ?? null)
        <div class="absolute inset-0 z-0 {{ ($data['overlay'] ?? true) ? 'opacity-50' : '' }}">
            <img src="{{ asset('storage/' . $data['image']) }}" alt="" class="w-full h-full object-cover">
        </div>
    @endif
    <div class="container relative z-10">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ $data['headline'] }}</h1>
        @if($data['subheadline'] ?? null)
            <p class="text-lg md:text-xl mb-8 text-slate-200">{{ $data['subheadline'] }}</p>
        @endif
        @if($data['cta_label'] ?? null)
            <a href="{{ $data['cta_url'] ?? '#' }}" class="btn btn-primary">
                {{ $data['cta_label'] }}
            </a>
        @endif
    </div>
</section>
