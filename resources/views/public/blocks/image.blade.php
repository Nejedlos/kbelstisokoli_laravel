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
                @php
                    $isStatic = str_contains($imageUrl, 'assets/img/');
                    $webpUrl = $isStatic ? str_replace(['.jpg', '.png'], '.webp', $imageUrl) : null;
                @endphp
                <picture>
                    @if($isStatic && file_exists(public_path($webpUrl)))
                        <source srcset="{{ asset($webpUrl) }}" type="image/webp">
                    @endif
                    <img src="{{ $imageUrl }}" alt="{{ $asset->alt_text ?? '' }}" class="w-full" loading="lazy" decoding="async" width="1600" height="900">
                </picture>
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
