@php
    $asset = isset($data['media_asset_id']) ? \App\Models\MediaAsset::find($data['media_asset_id']) : null;
    $imageUrl = $asset ? $asset->getUrl('large') : ($data['image_url'] ?? null);
    $variant = $data['variant'] ?? 'standard';
    $alignment = $data['alignment'] ?? ($variant === 'centered' ? 'center' : 'left');
@endphp

<section @class([
    'block-hero relative overflow-hidden min-h-[60vh] flex items-center',
    'bg-secondary text-white' => $variant !== 'minimal',
    'bg-white text-secondary' => $variant === 'minimal',
    'hero-gradient' => $variant === 'standard' && !$imageUrl,
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
    @elseif($variant === 'standard')
        <div class="absolute inset-0 z-0 hero-mesh opacity-20"></div>
    @endif

    {{-- Decor elements for "sexy" look --}}
    @if($variant !== 'minimal')
        <div class="absolute top-0 right-0 w-1/2 h-full bg-primary/5 -skew-x-12 translate-x-1/3 pointer-events-none z-0"></div>
        <div class="absolute bottom-0 right-0 w-1/4 h-1/2 border-r-[16px] border-b-[16px] border-primary/10 mr-8 mb-8 pointer-events-none z-0"></div>
        <div class="absolute top-1/2 left-0 w-64 h-64 bg-primary/10 blur-[120px] rounded-full pointer-events-none z-0"></div>
    @endif

    <div class="container relative z-10">
        <div @class([
            'max-w-3xl' => $alignment === 'left',
            'mx-auto text-center max-w-4xl' => $alignment === 'center',
            'ml-auto text-right max-w-3xl' => $alignment === 'right',
        ])>
            @if($data['eyebrow'] ?? null)
                <div class="mb-4 inline-flex items-center bg-primary/20 text-primary-light px-4 py-1.5 rounded-full text-xs md:text-sm font-black uppercase tracking-[0.2em] border border-primary/30">
                    <span class="w-2 h-2 bg-primary rounded-full mr-3 animate-pulse"></span>
                    {{ $data['eyebrow'] }}
                </div>
            @endif

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

            @if(($data['cta_label'] ?? null) || ($data['cta_secondary_label'] ?? null) || ($data['cta_tertiary_label'] ?? null))
                <div class="flex flex-wrap items-center gap-6 {{ $alignment === 'center' ? 'justify-center' : ($alignment === 'right' ? 'justify-end' : '') }}">
                    @if($data['cta_label'] ?? null)
                        <a href="{{ $data['cta_url'] ?? '#' }}" class="btn btn-primary btn-glow group">
                            <span>{{ $data['cta_label'] }}</span>
                            <i class="fa-light fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    @endif

                    @if($data['cta_secondary_label'] ?? null)
                        <a href="{{ $data['cta_secondary_url'] ?? '#' }}" class="btn btn-outline-white">
                            <span>{{ $data['cta_secondary_label'] }}</span>
                        </a>
                    @endif

                    @if($data['cta_tertiary_label'] ?? null)
                        <a href="{{ $data['cta_tertiary_url'] ?? '#' }}" class="text-white hover:text-primary transition-colors font-bold border-b border-white/30 hover:border-primary pb-1">
                            {{ $data['cta_tertiary_label'] }}
                        </a>
                    @endif
                </div>

                @if($data['microtext'] ?? null)
                    <div class="mt-6 text-sm text-slate-400 max-w-xl {{ $alignment === 'center' ? 'mx-auto' : ($alignment === 'right' ? 'ml-auto' : '') }}">
                        {{ $data['microtext'] }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Basketball graphic element --}}
    @if($variant === 'standard')
        <div class="hidden lg:block absolute right-12 top-1/2 -translate-y-1/2 w-1/3 opacity-20 pointer-events-none group-hover:rotate-45 transition-transform duration-1000">
            <i class="fa-light fa-basketball text-[25rem] rotate-12 text-primary/20"></i>
        </div>
    @endif
</section>
