@extends('layouts.public')

@section('title', __('search.title') . ($query ? ': ' . $query : ''))

@section('content')
    <div class="bg-slate-50 border-b border-slate-200 py-12">
        <div class="container">
            <x-breadcrumbs :breadcrumbs="$breadcrumbs ?? null" />

            <h1 class="font-display font-black text-4xl md:text-5xl uppercase tracking-tighter mb-6">
                {{ __('search.results_for') }}: <span class="text-primary italic">"{{ $query }}"</span>
            </h1>

            <form action="{{ route('public.search') }}" method="GET" class="max-w-2xl relative">
                <input type="text"
                       name="q"
                       value="{{ $query }}"
                       placeholder="{{ __('search.placeholder') }}"
                       class="w-full bg-white border-2 border-slate-200 rounded-xl px-6 py-4 text-lg focus:border-primary focus:ring-0 transition-all outline-none pr-16"
                       required
                       minlength="3">
                <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors">
                    <i class="fa-light fa-magnifying-glass text-2xl"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="container py-16">
        @if($results->isEmpty())
            <x-empty-state
                :title="__('search.no_results_title')"
                :subtitle="__('search.no_results_text')"
                icon="fa-basketball"
                :primaryCta="['url' => route('public.news.index'), 'label' => __('search.empty_cta_news')]"
                :secondaryCta="['url' => route('public.matches.index'), 'label' => __('search.empty_cta_matches')]"
            />
        @else
            <div class="grid gap-8 max-w-4xl">
                <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500 border-b border-slate-100 pb-4">
                    @php
                        $stats = $results->groupBy('type');
                    @endphp

                    <span class="font-bold text-slate-900">{{ __('search.total_results') }}: {{ $results->count() }}</span>
                    <span class="text-slate-200">|</span>

                    @foreach($stats as $type => $group)
                        <span>{{ $type }}: {{ $group->count() }}</span>
                        @if(!$loop->last) <span class="text-slate-200">•</span> @endif
                    @endforeach
                </div>

                @foreach($results as $result)
                    <article class="group relative flex flex-col md:flex-row gap-6 bg-white p-6 rounded-2xl hover:shadow-xl transition-all border border-slate-100">
                        @if($result->image)
                            <div class="w-full md:w-48 h-32 flex-shrink-0 overflow-hidden rounded-xl">
                                <x-picture
                                    :src="'storage/' . $result->image"
                                    :alt="$result->title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    loading="lazy"
                                />
                            </div>
                        @endif

                        <div class="flex-grow">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                    {{ $result->type }}
                                </span>
                                @if($result->date)
                                    <span class="text-slate-400 text-xs">{{ $result->date }}</span>
                                @endif
                            </div>

                            <h3 class="text-xl font-bold text-slate-900 group-hover:text-primary transition-colors mb-2">
                                <a href="{{ $result->url }}" class="after:absolute after:inset-0">
                                    {{ $result->title }}
                                </a>
                            </h3>

                            <p class="text-slate-600 line-clamp-2">
                                {{ $result->snippet }}
                            </p>
                        </div>

                        <div class="hidden md:flex items-center text-slate-300 group-hover:text-primary transition-colors">
                            <i class="fa-light fa-chevron-right text-xl"></i>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('head')
    <meta name="robots" content="noindex, follow">
@endpush
