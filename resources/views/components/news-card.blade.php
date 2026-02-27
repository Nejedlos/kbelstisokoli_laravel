@props([
    'post'
])

<article class="group bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 flex flex-col h-full relative">
    {{-- Dekorativní prvek pro basketbalový nádech --}}
    <div class="absolute top-0 right-0 w-32 h-32 opacity-[0.03] text-secondary pointer-events-none transform translate-x-1/2 -translate-y-1/2 rotate-12 z-20 group-hover:opacity-[0.08] transition-opacity">
        <i class="fa-light fa-basketball text-8xl"></i>
    </div>

    @if($post->featured_image)
        <div class="aspect-[16/10] overflow-hidden relative">
            <x-picture
                :src="'storage/' . $post->featured_image"
                :alt="$post->title"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                loading="lazy"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        </div>
    @else
        <div class="aspect-[16/10] bg-slate-50 flex items-center justify-center relative overflow-hidden group-hover:bg-slate-100 transition-colors">
            <i class="fa-light fa-newspaper text-6xl text-slate-200 group-hover:text-primary/20 transition-colors duration-500"></i>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-[0.02] text-secondary group-hover:opacity-[0.05] transition-opacity duration-500">
                <i class="fa-light fa-basketball text-[120px]"></i>
            </div>
        </div>
    @endif

    <div class="p-8 flex flex-col flex-1 relative z-10">
        <div class="flex items-center space-x-3 mb-5">
            @if($post->category)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest">
                    {{ $post->category->name }}
                </span>
            @endif
            <div class="flex items-center text-[11px] font-bold uppercase tracking-widest text-slate-400">
                <i class="fa-light fa-calendar-days mr-2"></i>
                <time datetime="{{ $post->publish_at?->toW3cString() ?? $post->created_at->toW3cString() }}">
                    {{ ($post->publish_at ?? $post->created_at)->format('d. m. Y') }}
                </time>
            </div>
        </div>

        <h3 class="text-2xl font-black mb-4 leading-tight text-secondary group-hover:text-primary transition-colors duration-300">
            <a href="{{ route('public.news.show', $post->slug) }}">
                {{ $post->title }}
            </a>
        </h3>

        @if($post->excerpt)
            <p class="text-slate-500 line-clamp-3 mb-8 flex-1 leading-relaxed">
                {{ $post->excerpt }}
            </p>
        @endif

        <div class="mt-auto pt-6 border-t border-slate-50">
            <a href="{{ route('public.news.show', $post->slug) }}" class="inline-flex items-center text-xs font-black uppercase tracking-[0.2em] text-secondary group-hover:text-primary transition-all duration-300">
                <span>{{ __('news.view_detail') }}</span>
                <i class="fa-light fa-arrow-right-long ml-3 transform group-hover:translate-x-2 transition-transform duration-300"></i>
            </a>
        </div>
    </div>
</article>
