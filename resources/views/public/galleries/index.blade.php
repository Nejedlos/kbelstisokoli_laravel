@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('gallery.title')"
        :subtitle="__('gallery.subtitle')"
        :breadcrumbs="[__('gallery.breadcrumb') => null]"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            {{-- Sekce: Náhodný výběr z poolu --}}
            @if($randomPhotos->isNotEmpty())
                <div class="mb-20">
                    <x-section-heading
                        :title="__('gallery.random_title')"
                        :subtitle="__('gallery.random_subtitle')"
                        alignment="left"
                    />
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($randomPhotos as $photo)
                            <a
                                href="{{ $photo->getUrl('optimized') }}"
                                class="spotlight group relative aspect-square overflow-hidden rounded-xl bg-slate-200 shadow-sm"
                                data-group="random-moments"
                                data-caption="{{ $photo->title }}"
                            >
                                <img
                                    src="{{ $photo->getUrl('thumb') }}"
                                    alt="{{ $photo->alt_text ?: $photo->title }}"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                    loading="lazy"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex items-end p-3">
                                    <p class="text-white text-[10px] font-bold text-left line-clamp-2">
                                        {{ $photo->title }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Sekce: Sbírky fotografií (Photo Pools) --}}
            @if($pools->isNotEmpty())
                <div class="mb-20">
                    <x-section-heading
                        :title="__('gallery.pools_title')"
                        :subtitle="__('gallery.pools_subtitle')"
                        alignment="left"
                    />
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($pools as $pool)
                            <a href="{{ route('public.galleries.show', $pool->slug) }}" class="card card-hover flex flex-col h-full group">
                                <div class="aspect-video relative overflow-hidden bg-slate-200">
                                    @php
                                        $cover = $pool->mediaAssets->first();
                                    @endphp
                                    @if($cover)
                                        <img
                                            src="{{ $cover->getUrl('optimized') }}"
                                            alt="{{ $pool->getTranslation('title', app()->getLocale()) }}"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                        >
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-400">
                                            <i class="fa-light fa-images text-5xl"></i>
                                        </div>
                                    @endif
                                    <div class="absolute top-4 left-4">
                                        <span class="px-3 py-1 bg-primary text-white text-xs font-black uppercase tracking-widest rounded-full shadow-lg">
                                            {{ $pool->mediaAssets->count() }} {{ __('gallery.photos') }}
                                        </span>
                                    </div>
                                    <div class="absolute top-4 right-4">
                                        <span class="px-3 py-1 bg-secondary/80 backdrop-blur-md text-white text-[10px] font-black uppercase tracking-widest rounded-full shadow-lg">
                                            @if($pool->event_date?->day === 1 && $pool->event_date?->month === 1)
                                                {{ $pool->event_date?->format('Y') }}
                                            @else
                                                {{ $pool->event_date?->format('d. m. Y') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="p-6 flex-1 flex flex-col">
                                    <h3 class="text-xl font-bold mb-2 group-hover:text-primary transition-colors line-clamp-2">
                                        {{ brand_text($pool->getTranslation('title', app()->getLocale())) }}
                                    </h3>
                                    @if($pool->team)
                                        <div class="mb-3">
                                            <span class="text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md">
                                                <i class="fa-light fa-users-group mr-1"></i> {{ $pool->team->name }}
                                            </span>
                                        </div>
                                    @endif
                                    @if($pool->getTranslation('description', app()->getLocale()))
                                        <p class="text-slate-600 text-sm line-clamp-2 mb-4">
                                            {{ brand_text($pool->getTranslation('description', app()->getLocale())) }}
                                        </p>
                                    @endif
                                    <div class="mt-auto pt-4 flex items-center text-primary text-sm font-black uppercase tracking-widest">
                                        {{ __('gallery.view_pool') }}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Sekce: Klasické galerie --}}
            @if($galleries->isNotEmpty())
                <div>
                    <x-section-heading
                        :title="__('gallery.other_galleries')"
                        :subtitle="__('gallery.other_galleries_subtitle')"
                        alignment="left"
                    />
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($galleries as $gallery)
                            <a href="{{ route('public.galleries.show', $gallery->slug) }}" class="card card-hover flex flex-col h-full group">
                                <div class="aspect-video relative overflow-hidden bg-slate-200">
                                    @if($gallery->coverAsset)
                                        <img
                                            src="{{ $gallery->coverAsset->getUrl('large') }}"
                                            alt="{{ $gallery->title }}"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                        >
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-400">
                                            <i class="fa-light fa-image text-5xl"></i>
                                        </div>
                                    @endif
                                    <div class="absolute top-4 left-4">
                                        <span class="px-3 py-1 bg-primary text-white text-xs font-black uppercase tracking-widest rounded-full shadow-lg">
                                            {{ $gallery->media_assets_count ?: $gallery->mediaAssets->count() }} fotek
                                        </span>
                                    </div>
                                </div>
                                <div class="p-6 flex-1 flex flex-col">
                                    <h3 class="text-xl font-bold mb-2 group-hover:text-primary transition-colors line-clamp-2">
                                        {{ brand_text($gallery->title) }}
                                    </h3>
                                    @if($gallery->description)
                                        <p class="text-slate-600 text-sm line-clamp-2 mb-4">
                                            {{ brand_text($gallery->description) }}
                                        </p>
                                    @endif
                                    <div class="mt-auto pt-4 flex items-center text-primary text-sm font-black uppercase tracking-widest">
                                        {{ __('gallery.view_gallery') }}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($galleries->isEmpty() && $pools->isEmpty())
                <x-empty-state
                    :title="__('gallery.none_title')"
                    :subtitle="__('gallery.none_subtitle')"
                />
            @endif
        </div>
    </div>
@endsection
