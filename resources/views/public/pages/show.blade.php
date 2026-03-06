@extends('layouts.public')

@section('content')
    @php
        $firstBlock = $page->content[0] ?? null;
        $hasHero = $firstBlock && $firstBlock['type'] === 'hero';
    @endphp

    @if(!$hasHero)
        <x-page-header
            :title="brand_text($page->title)"
            :breadcrumbs="$breadcrumbs ?? null"
            image="assets/img/hero/hero-news.webp"
        />
    @endif

    {{-- Renderování bloků --}}
    <x-page-blocks :blocks="$page->content ?? []" :breadcrumbs="$hasHero ? $breadcrumbs : null" />
@endsection
