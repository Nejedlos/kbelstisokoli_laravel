@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('news.title')"
        :subtitle="__('news.subtitle')"
        :breadcrumbs="[__('news.breadcrumbs') => null]"
    />

    <div class="section-padding bg-bg relative overflow-hidden">
        {{-- Sportovní background hinty (nízká opacita) --}}
        <div class="absolute top-0 right-0 p-24 opacity-[0.03] text-secondary pointer-events-none transform translate-x-1/2 -translate-y-1/2 rotate-12">
            <i class="fa-light fa-basketball-hoop text-[400px]"></i>
        </div>
        <div class="absolute bottom-0 left-0 p-24 opacity-[0.03] text-primary pointer-events-none transform -translate-x-1/4 translate-y-1/4 -rotate-12">
            <i class="fa-light fa-whistle text-[300px]"></i>
        </div>
        <div class="absolute top-1/2 left-0 p-24 opacity-[0.02] text-secondary pointer-events-none transform -translate-x-1/2 -translate-y-1/2">
            <i class="fa-light fa-basketball text-[500px]"></i>
        </div>

        <div class="container relative z-10">
            @if($posts->isEmpty())
                <x-empty-state
                    :title="__('news.empty_title')"
                    :subtitle="__('news.empty_subtitle')"
                    icon="fa-light fa-newspaper"
                    :primaryCta="['url' => route('public.matches.index'), 'label' => __('news.empty_cta_matches')]"
                    :secondaryCta="['url' => route('public.contact.index'), 'label' => __('news.empty_cta_contact')]"
                />

                <div class="mt-20 max-w-5xl mx-auto border-t border-slate-200/60 pt-16">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                        <div>
                            <div class="inline-flex items-center px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-black uppercase tracking-widest mb-6">
                                <i class="fa-light fa-star mr-2"></i>
                                {{ __('news.coming_soon') }}
                            </div>
                            <h3 class="text-3xl md:text-4xl font-black uppercase tracking-tighter mb-6 text-secondary leading-none">
                                {{ __('news.starter_title') }}
                            </h3>
                            <p class="text-slate-500 leading-relaxed text-lg text-balance mb-8">
                                {{ __('news.starter_text') }}
                            </p>
                            <div class="flex flex-wrap gap-4">
                                <a href="{{ route('public.contact.index') }}" class="btn btn-secondary btn-sm">
                                    {{ __('news.suggest_topic') }}
                                </a>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-6">
                            <div class="aspect-square bg-white rounded-3xl flex items-center justify-center shadow-sm border border-slate-100 p-8 text-center group hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                                <div>
                                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-primary/5 transition-colors">
                                        <i class="fa-light fa-camera-retro text-3xl text-primary/40 group-hover:text-primary transition-colors"></i>
                                    </div>
                                    <div class="text-[11px] font-black uppercase tracking-widest text-slate-400 group-hover:text-secondary transition-colors">{{ __('news.starter_media') }}</div>
                                </div>
                            </div>
                            <div class="aspect-square bg-white rounded-3xl flex items-center justify-center shadow-sm border border-slate-100 p-8 text-center group hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                                <div>
                                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-primary/5 transition-colors">
                                        <i class="fa-light fa-microphone-stand text-3xl text-primary/40 group-hover:text-primary transition-colors"></i>
                                    </div>
                                    <div class="text-[11px] font-black uppercase tracking-widest text-slate-400 group-hover:text-secondary transition-colors">{{ __('news.starter_interviews') }}</div>
                                </div>
                            </div>
                            <div class="aspect-square bg-white rounded-3xl flex items-center justify-center shadow-sm border border-slate-100 p-8 text-center group hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                                <div>
                                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-primary/5 transition-colors">
                                        <i class="fa-light fa-chart-line-up text-3xl text-primary/40 group-hover:text-primary transition-colors"></i>
                                    </div>
                                    <div class="text-[11px] font-black uppercase tracking-widest text-slate-400 group-hover:text-secondary transition-colors">{{ __('news.starter_reports') }}</div>
                                </div>
                            </div>
                            <div class="aspect-square bg-white rounded-3xl flex items-center justify-center shadow-sm border border-slate-100 p-8 text-center group hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                                <div>
                                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-primary/5 transition-colors">
                                        <i class="fa-light fa-bullhorn text-3xl text-primary/40 group-hover:text-primary transition-colors"></i>
                                    </div>
                                    <div class="text-[11px] font-black uppercase tracking-widest text-slate-400 group-hover:text-secondary transition-colors">{{ __('news.starter_updates') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
                    @foreach($posts as $post)
                        <x-news-card :post="$post" />
                    @endforeach
                </div>

                <div class="mt-16 flex justify-center">
                    {{ $posts->links('pagination.public') }}
                </div>
            @endif
        </div>
    </div>
@endsection
