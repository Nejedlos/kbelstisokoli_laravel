@php
    $asset = isset($data['media_asset_id']) ? \App\Models\MediaAsset::find($data['media_asset_id']) : null;
    $imageUrl = $asset ? $asset->getUrl('large') : null;
    $variant = $data['variant'] ?? 'standard';
    $alignment = $data['alignment'] ?? ($variant === 'centered' ? 'center' : 'left');
@endphp

<section @class([
    'block-hero relative overflow-hidden min-h-[60vh] flex items-center',
    'bg-secondary text-white' => $variant !== 'minimal',
    'bg-white text-secondary' => $variant === 'minimal',
    'py-20 md:py-32' => $variant === 'centered',
    'py-12 md:py-20' => $variant !== 'centered',
])>
    {{-- Background Image / Overlay --}}
    @if($imageUrl && $variant !== 'minimal')
        <div class="absolute inset-0 z-0">
            <img src="{{ $imageUrl }}" alt="{{ $asset->alt_text ?? '' }}" class="w-full h-full object-cover">
            @if($data['overlay'] ?? true)
                <div class="absolute inset-0 bg-gradient-to-r from-secondary/95 via-secondary/70 to-transparent"></div>
            @endif
        </div>
    @endif

    {{-- Decor elements for "sexy" look --}}
    @if($variant !== 'minimal')
        <div class="absolute top-0 right-0 w-1/3 h-full bg-primary/10 -skew-x-12 translate-x-1/2 pointer-events-none z-0"></div>
        <div class="absolute bottom-0 right-0 w-1/4 h-1/2 border-r-8 border-b-8 border-primary/20 mr-12 mb-12 pointer-events-none z-0"></div>
    @endif

    <div class="container relative z-10">
        <div @class([
            'max-w-3xl' => $alignment === 'left',
            'mx-auto text-center max-w-4xl' => $alignment === 'center',
            'ml-auto text-right max-w-3xl' => $alignment === 'right',
        ])>
            @if($data['headline'] ?? null)
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-black mb-6 leading-tight uppercase tracking-tighter">
                    {!! nl2br(e($data['headline'])) !!}
                </h1>
            @endif

            @if($data['subheadline'] ?? null)
                <p class="text-lg md:text-2xl mb-10 text-slate-300 font-medium leading-relaxed italic border-l-4 border-primary pl-6 py-2">
                    {{ $data['subheadline'] }}
                </p>
            @endif

            @if($data['cta_label'] ?? null)
                <div class="flex flex-wrap gap-4 {{ $alignment === 'center' ? 'justify-center' : ($alignment === 'right' ? 'justify-end' : '') }}">
                    <a href="{{ $data['cta_url'] ?? '#' }}" class="btn btn-primary btn-glow group">
                        <span>{{ $data['cta_label'] }}</span>
                        <i class="fa-light fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Basketball graphic element --}}
    @if($variant === 'standard')
        <div class="hidden lg:block absolute right-0 top-1/2 -translate-y-1/2 w-1/3 opacity-10 pointer-events-none">
            <i class="fa-light fa-basketball text-[20rem] rotate-12"></i>
        </div>
    @endif
</section>
