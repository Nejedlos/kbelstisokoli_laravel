@php
    $asset = isset($data['media_asset_id']) ? \App\Models\MediaAsset::find($data['media_asset_id']) : null;
    $imageUrl = $asset ? $asset->getUrl('large') : ($data['image_url'] ?? null);
    $videoUrl = $data['video_url'] ?? null;
    $variant = $data['variant'] ?? 'standard';
    $alignment = $data['alignment'] ?? ($variant === 'centered' ? 'center' : 'left');
@endphp

<section @class([
    'block-hero relative overflow-hidden min-h-[60vh] flex items-center',
    'bg-secondary text-white' => $variant !== 'minimal',
    'bg-white text-secondary' => $variant === 'minimal',
    'hero-gradient' => $variant === 'standard' && !$imageUrl && !$videoUrl,
    'py-20 md:py-32' => $variant === 'centered',
    'py-16 md:py-24' => $variant !== 'centered',
])>
    {{-- Background Image / Video / Overlay --}}
    @if(($imageUrl || $videoUrl) && $variant !== 'minimal')
        <div class="absolute inset-0 z-0">
            @if($videoUrl)
                <video
                    autoplay
                    muted
                    loop
                    playsinline
                    poster="{{ $imageUrl }}"
                    class="w-full h-full object-cover"
                >
                    <source src="{{ asset($videoUrl) }}" type="video/mp4">
                </video>
            @else
                <img src="{{ $imageUrl }}" alt="{{ $asset->alt_text ?? '' }}" class="w-full h-full object-cover">
            @endif

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
        @if(isset($breadcrumbs) && $breadcrumbs)
            <div @class([
                'mb-4',
                'flex justify-center' => $alignment === 'center',
                'flex justify-end' => $alignment === 'right',
            ])>
                <x-breadcrumbs :breadcrumbs="$breadcrumbs" variant="light" />
            </div>
        @endif

        <div @class([
            'max-w-3xl' => $alignment === 'left',
            'mx-auto text-center max-w-4xl' => $alignment === 'center',
            'ml-auto text-right max-w-3xl' => $alignment === 'right',
        ])>
            @if($data['eyebrow'] ?? null)
                <div @class([
                    'mb-6 inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-display font-bold uppercase tracking-[0.3em] whitespace-nowrap max-w-full overflow-hidden leading-none shadow-2xl',
                    'bg-white/5 backdrop-blur-md text-primary-light border border-white/10 shadow-black/20' => $variant !== 'minimal',
                    'bg-secondary/5 text-secondary border border-secondary/10 shadow-secondary/5' => $variant === 'minimal',
                ])>
                    <span class="relative flex h-2 w-2 mr-4 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                    </span>
                    {{ $data['eyebrow'] }}
                </div>
            @endif

            @if($data['headline'] ?? null)
                <h1 class="text-3xl sm:text-4xl md:text-6xl lg:text-7xl font-black mb-6 leading-display uppercase tracking-tighter text-balance">
                    {!! nl2br(e($data['headline'])) !!}
                </h1>
            @endif

            @if($data['subheadline'] ?? null)
                <p class="text-base sm:text-lg md:text-xl lg:text-2xl mb-10 text-white/80 font-medium leading-relaxed italic border-l-4 border-primary pl-4 md:pl-6 py-2 text-balance">
                    {{ $data['subheadline'] }}
                </p>
            @endif

            @if(($data['cta_label'] ?? null) || ($data['cta_secondary_label'] ?? null) || ($data['cta_tertiary_label'] ?? null))
                <div class="flex flex-wrap items-center gap-4 sm:gap-6 {{ $alignment === 'center' ? 'justify-center' : ($alignment === 'right' ? 'justify-end' : '') }}">
                    @if($data['cta_label'] ?? null)
                        <a href="{{ $data['cta_url'] ?? '#' }}" class="btn btn-primary btn-glow group w-full sm:w-auto">
                            <span>{{ $data['cta_label'] }}</span>
                            <i class="fa-light fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    @endif

                    @if($data['cta_secondary_label'] ?? null)
                        <a href="{{ $data['cta_secondary_url'] ?? '#' }}" class="btn btn-outline-white w-full sm:w-auto">
                            <span>{{ $data['cta_secondary_label'] }}</span>
                        </a>
                    @endif

                    @if($data['cta_tertiary_label'] ?? null)
                        <a href="{{ $data['cta_tertiary_url'] ?? '#' }}" target="_blank" rel="noopener" class="inline-flex items-center gap-4 text-[min(4.2vw,0.875rem)] sm:text-sm font-black uppercase tracking-widest-responsive text-white hover:text-white transition-all group py-4 px-5 sm:py-3 sm:px-6 bg-secondary/80 sm:bg-secondary/40 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl mt-4 sm:mt-0 leading-tight">
                            <span class="relative border-b border-white/30 group-hover:border-primary transition-colors pb-1 leading-tight text-balance">
                                {{ $data['cta_tertiary_label'] }}
                            </span>
                            <span class="w-12 h-12 sm:w-10 sm:h-10 rounded-full bg-primary flex items-center justify-center shrink-0 transition-all duration-300 group-hover:scale-110 shadow-lg shadow-primary/40">
                                <i class="fa-solid fa-arrow-up-right text-lg sm:text-sm text-white group-hover:-translate-y-0.5 group-hover:translate-x-0.5 transition-transform duration-300"></i>
                            </span>
                        </a>
                    @endif
                </div>

                @if($data['microtext'] ?? null)
                    <div class="mt-6 text-sm text-white/60 max-w-xl {{ $alignment === 'center' ? 'mx-auto' : ($alignment === 'right' ? 'ml-auto' : '') }} leading-snug">
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
