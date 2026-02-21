@php
    $gallery = isset($data['gallery_id']) ? \App\Models\Gallery::with('mediaAssets')->find($data['gallery_id']) : null;
    $layout = $data['layout'] ?? 'grid';
@endphp

<section class="block-gallery section-padding">
    <div class="container">
        @if($data['title'] ?? $gallery?->title)
            <x-section-heading
                :title="brand_text($data['title'] ?? $gallery?->title)"
                :subtitle="brand_text($gallery?->description)"
                align="center"
            />
        @endif

        @if($gallery && $gallery->mediaAssets->isNotEmpty())
            <div @class([
                'grid gap-4 md:gap-6',
                'grid-cols-2 md:grid-cols-3 lg:grid-cols-4' => $layout === 'grid',
                'columns-2 md:columns-3 lg:columns-4 space-y-4 md:space-y-6' => $layout === 'masonry',
            ])>
                @foreach($gallery->mediaAssets as $asset)
                    @if($asset->pivot->is_visible)
                        <div @class([
                            'card overflow-hidden group cursor-pointer',
                            'aspect-square' => $layout === 'grid',
                            'break-inside-avoid' => $layout === 'masonry',
                        ])>
                            <img
                                src="{{ $asset->getUrl($layout === 'grid' ? 'thumb' : 'large') }}"
                                alt="{{ $asset->alt_text ?? '' }}"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                loading="lazy"
                            >
                            @if($asset->pivot->caption_override || $asset->caption)
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                    <p class="text-white text-sm font-medium line-clamp-2">
                                        {{ $asset->pivot->caption_override ?: $asset->caption }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <x-empty-state
                title="Galerie je prázdná"
                subtitle="Tato galerie zatím neobsahuje žádné viditelné snímky."
            />
        @endif
    </div>
</section>
