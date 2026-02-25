@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('news.title')"
        :subtitle="__('news.subtitle')"
        :breadcrumbs="[__('news.breadcrumbs') => null]"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            @if($posts->isEmpty())
                <x-empty-state
                    :title="__('news.empty_title')"
                    :subtitle="__('news.empty_subtitle')"
                />
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($posts as $post)
                        <x-news-card :post="$post" />
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
