@extends('layouts.public')

@section('content')
    <x-page-header
        :title="brand_text($gallery->title)"
        :subtitle="brand_text($gallery->description)"
        :image="$gallery->coverAsset ? $gallery->coverAsset->getUrl('large') : null"
        :breadcrumbs="['Galerie' => route('public.galleries.index'), brand_text($gallery->title) => null]"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            @if($gallery->mediaAssets->isEmpty())
                <x-empty-state
                    title="Galerie je prázdná"
                    subtitle="Tato galerie zatím neobsahuje žádné snímky."
                />
            @else
                <div @class([
                    'grid gap-4 md:gap-8',
                    'grid-cols-2 md:grid-cols-3 lg:grid-cols-4' => $gallery->variant === 'grid',
                    'columns-2 md:columns-3 lg:columns-4 space-y-4 md:space-y-8' => $gallery->variant === 'masonry',
                ])>
                    @foreach($gallery->mediaAssets as $asset)
                        @if($asset->pivot->is_visible)
                            @php
                                $caption = $asset->pivot->caption_override ?: ($asset->caption ?: $asset->title);
                            @endphp
                            <a
                                href="{{ $asset->getUrl('optimized') }}"
                                @class([
                                    'spotlight card overflow-hidden group cursor-pointer relative',
                                    'aspect-square' => $gallery->variant === 'grid',
                                    'break-inside-avoid' => $gallery->variant === 'masonry',
                                ])
                                data-group="gallery-{{ $gallery->id }}"
                                data-caption="{{ $caption }}"
                            >
                                <img
                                    src="{{ $asset->getUrl($gallery->variant === 'grid' ? 'thumb' : 'optimized') }}"
                                    alt="{{ $asset->alt_text ?? '' }}"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                    loading="lazy"
                                >
                                @if($caption)
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                        <p class="text-white text-sm font-medium line-clamp-2">
                                            {{ $caption }}
                                        </p>
                                    </div>
                                @endif
                            </a>
                        @endif
                    @endforeach
                </div>

                <div class="mt-16 pt-8 border-t border-slate-200">
                    <a href="{{ route('public.galleries.index') }}" class="btn btn-outline">
                        &larr; Zpět na seznam galerií
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
