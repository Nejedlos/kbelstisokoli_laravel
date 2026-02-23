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
            <div class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-light fa-magnifying-glass text-3xl text-slate-400"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2">{{ __('search.no_results_title') }}</h2>
                <p class="text-slate-500 max-w-md mx-auto">
                    {{ __('search.no_results_text') }}
                </p>
                <div class="mt-8">
                    <a href="{{ route('public.home') }}" class="btn btn-outline">
                        {{ __('search.back_home') }}
                    </a>
                </div>
            </div>
        @else
            <div class="grid gap-8 max-w-4xl">
                <div class="flex items-center justify-between text-sm text-slate-500 border-b border-slate-100 pb-4">
                    <span>{{ __('search.found_count', ['count' => $results->count()]) }}</span>
                </div>

                @foreach($results as $result)
                    <article class="group relative flex flex-col md:flex-row gap-6 bg-white p-6 rounded-2xl hover:shadow-xl transition-all border border-slate-100">
                        @if($result->image)
                            <div class="w-full md:w-48 h-32 flex-shrink-0 overflow-hidden rounded-xl">
                                <img src="{{ asset('storage/' . $result->image) }}" alt="{{ $result->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
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
