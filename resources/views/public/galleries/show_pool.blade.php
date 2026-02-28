@extends('layouts.public')

@section('content')
    <x-page-header
        :title="brand_text($pool->getTranslation('title', app()->getLocale()))"
        :subtitle="($pool->event_date ? ($pool->event_date->day === 1 && $pool->event_date->month === 1 ? $pool->event_date->format('Y') : $pool->event_date->format('d. m. Y')) . ($pool->getTranslation('description', app()->getLocale()) ? ' • ' : '') : '') . brand_text($pool->getTranslation('description', app()->getLocale()))"
        :image="$pool->mediaAssets->first() ? $pool->mediaAssets->first()->getUrl('optimized') : null"
        :breadcrumbs="[__('gallery.breadcrumb') => route('public.galleries.index'), brand_text($pool->getTranslation('title', app()->getLocale())) => null]"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            @if($pool->mediaAssets->isEmpty())
                <x-empty-state
                    :title="__('gallery.empty_pool_title')"
                    :subtitle="__('gallery.empty_pool_subtitle')"
                />
            @else
                <div class="grid gap-4 md:gap-8 grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    @foreach($pool->mediaAssets as $asset)
                        <a
                            href="{{ $asset->getUrl('optimized') }}"
                            class="spotlight card overflow-hidden group cursor-pointer relative aspect-square"
                            data-group="pool-gallery"
                            data-caption="{{ $asset->pivot->caption_override ?: $asset->title }}"
                        >
                            <img
                                src="{{ $asset->getUrl('thumb') }}"
                                alt="{{ $asset->alt_text ?: $asset->title }}"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                loading="lazy"
                            >
                            @php
                                $caption = $asset->pivot->caption_override ?: $asset->title;
                            @endphp
                            @if($caption)
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                    <p class="text-white text-sm font-medium line-clamp-2">
                                        {{ $caption }}
                                    </p>
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>

                <div class="mt-16 pt-8 border-t border-slate-200">
                    <a href="{{ route('public.galleries.index') }}" class="btn btn-outline border-slate-200 hover:border-primary">
                        <i class="fa-light fa-arrow-left mr-2"></i> {{ __('gallery.back_to_list') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
