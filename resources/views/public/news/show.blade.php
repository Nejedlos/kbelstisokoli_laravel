@extends('layouts.public')

@section('content')
    <article>
        <x-page-header
            :title="$post->title"
            :image="$post->featured_image ? 'storage/' . $post->featured_image : null"
            :breadcrumbs="[__('news.breadcrumbs') => route('public.news.index'), $post->title => null]"
        />

        <div class="section-padding bg-white">
            <div class="container max-w-4xl">
                <div class="flex items-center space-x-6 mb-8 pb-8 border-b border-slate-100 text-sm font-bold uppercase tracking-widest text-slate-500">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ ($post->publish_at ?? $post->created_at)->format('d. m. Y') }}
                    </div>
                    @if($post->category)
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            {{ $post->category->name }}
                        </div>
                    @endif
                </div>

                @if($post->excerpt)
                    <p class="text-xl md:text-2xl text-slate-600 font-medium leading-relaxed mb-12 italic border-l-4 border-primary pl-8">
                        {{ brand_text($post->excerpt) }}
                    </p>
                @endif

                <div class="prose prose-slate prose-lg md:prose-xl max-w-none prose-headings:font-display prose-headings:uppercase prose-headings:tracking-tight prose-a:text-primary prose-img:rounded-club">
                    {!! brand_text($post->content) !!}
                </div>

                <div class="mt-16 pt-8 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('public.news.index') }}" class="btn btn-outline-primary py-2 px-6">
                        &larr; {{ __('news.back_to_news') }}
                    </a>

                    {{-- Placeholder pro social sharing --}}
                    <div class="flex items-center space-x-4">
                        <span class="text-xs font-black uppercase tracking-widest text-slate-400">{{ app()->getLocale() === 'cs' ? 'Sdílet:' : 'Share:' }}</span>
                        <div class="flex space-x-2">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-primary hover:text-white transition-colors cursor-pointer">F</div>
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-primary hover:text-white transition-colors cursor-pointer">X</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </article>
@endsection
