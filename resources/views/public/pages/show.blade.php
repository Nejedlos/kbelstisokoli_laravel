@extends('layouts.public')

@section('content')
    <div class="container">
        <x-breadcrumbs :breadcrumbs="$breadcrumbs ?? null" />
    </div>

    {{-- Pro CMS stránky typicky Page Header nepotřebujeme, protože mají Hero blok --}}
    {{-- Ale pokud by stránka neměla žádný viditelný blok, zobrazíme aspoň titulek --}}
    @if(empty($page->content))
        <x-page-header :title="brand_text($page->title)" />
    @endif

    {{-- Renderování bloků --}}
    <x-page-blocks :blocks="$page->content ?? []" />
@endsection
