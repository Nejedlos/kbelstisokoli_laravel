@extends('layouts.public')

@section('content')
    <div class="page-header bg-gray-100 py-12 mb-8">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl font-black text-gray-900">{{ $page->title }}</h1>
        </div>
    </div>

    {{-- Renderování bloků --}}
    <x-page-blocks :blocks="$page->content ?? []" />
@endsection
