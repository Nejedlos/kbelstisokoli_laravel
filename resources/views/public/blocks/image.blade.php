@php
    $asset = isset($data['media_asset_id']) ? \App\Models\MediaAsset::find($data['media_asset_id']) : null;
    $imageUrl = $asset ? $asset->getUrl('large') : null;
    $caption = $data['caption'] ?? $asset?->caption;
    $widthClass = $data['width_class'] ?? 'max-w-full';
@endphp

<section class="block-image section-padding">
    <div class="container flex justify-center">
        @if($imageUrl)
            <figure class="overflow-hidden rounded-club shadow-club {{ $widthClass }}">
                <x-picture
                    :src="$imageUrl"
                    :alt="$asset->alt_text ?? ($data['caption'] ?? '')"
                    class="w-full"
                    loading="lazy"
                    decoding="async"
                    width="1600"
                    height="900"
                />
                @if($caption)
                    <figcaption class="p-4 bg-slate-50 text-center italic text-slate-600 border-t border-slate-100">
                        {{ $caption }}
                    </figcaption>
                @endif
            </figure>
        @else
            <x-empty-state title="Obrázek chybí" subtitle="Obrázek nebyl v knihovně nalezen." />
        @endif
    </div>
</section>
