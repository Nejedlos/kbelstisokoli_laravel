@props([
    'post'
])

<article class="card card-hover flex flex-col h-full">
    @if($post->featured_image)
        <div class="aspect-video overflow-hidden">
            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover transition-transform duration-500 hover:scale-105">
        </div>
    @endif

    <div class="p-6 flex flex-col flex-1">
        <div class="flex items-center space-x-4 mb-3 text-xs font-bold uppercase tracking-widest text-slate-500">
            @if($post->category)
                <span class="text-primary">{{ $post->category->name }}</span>
                <span>/</span>
            @endif
            <time datetime="{{ $post->publish_at?->toW3cString() ?? $post->created_at->toW3cString() }}">
                {{ ($post->publish_at ?? $post->created_at)->format('d. m. Y') }}
            </time>
        </div>

        <h3 class="text-xl md:text-2xl font-black mb-3 group-hover:text-primary transition-colors">
            <a href="{{ route('public.news.show', $post->slug) }}">
                {{ $post->title }}
            </a>
        </h3>

        @if($post->excerpt)
            <p class="text-slate-600 line-clamp-3 mb-6 flex-1">
                {{ $post->excerpt }}
            </p>
        @endif

        <div class="mt-auto">
            <a href="{{ route('public.news.show', $post->slug) }}" class="inline-flex items-center text-sm font-black uppercase tracking-widest text-secondary hover:text-primary transition-colors">
                Číst více
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>
</article>
