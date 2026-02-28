@cacheFragment('block_news_listing_' . ($data['limit'] ?? 3) . '_' . app()->getLocale(), 3600)
@php
    $news = \App\Models\Post::where('is_visible', true)
        ->where('status', 'published')
        ->where(function ($query) {
            $query->whereNull('publish_at')
                ->orWhere('publish_at', '<=', now());
        })
        ->orderBy('publish_at', 'desc')
        ->orderBy('created_at', 'desc')
        ->take($data['limit'] ?? 3)
        ->get();
@endphp

<section class="block-news-listing section-padding">
    <div class="container">
        <x-section-heading :title="($data['title'] ?? __('news.title'))" :subtitle="($data['subtitle'] ?? __('news.subtitle'))" align="center" />

        @if($news->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($news as $post)
                    <x-news-card :post="$post" />
                @endforeach
            </div>

            <div class="mt-16 text-center">
                <a href="{{ route('public.news.index') }}" class="btn btn-primary px-10">
                    {{ __('news.all_news') }}
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <x-empty-state class="col-span-full" :title="__('news.empty_title')" :subtitle="__('news.empty_subtitle')" />
            </div>
        @endif
    </div>
</section>
@endCacheFragment
