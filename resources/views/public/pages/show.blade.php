@extends('layouts.public')

@section('content')
    {{-- Pro CMS stránky typicky Page Header nepotřebujeme, protože mají Hero blok --}}
    {{-- Ale pokud by stránka neměla žádný viditelný blok, zobrazíme aspoň titulek --}}
    @if(empty($page->content))
        <x-page-header :title="$page->title" />
    @endif

    {{-- Renderování bloků --}}
    <x-page-blocks :blocks="$page->content ?? []" />
@endsection
