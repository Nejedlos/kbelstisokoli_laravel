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
                    icon="fa-newspaper"
                    :primaryCta="['url' => route('public.matches.index'), 'label' => __('news.empty_cta_matches')]"
                    :secondaryCta="['url' => route('public.contact.index'), 'label' => __('news.empty_cta_contact')]"
                />

                <div class="mt-20 max-w-4xl mx-auto border-t border-slate-100 pt-16">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                        <div>
                            <h3 class="text-2xl font-black uppercase tracking-tighter mb-4 text-secondary">{{ __('news.starter_title') }}</h3>
                            <p class="text-slate-500 leading-relaxed text-balance">
                                {{ __('news.starter_text') }}
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="aspect-square bg-slate-50 rounded-2xl flex items-center justify-center border border-slate-100 p-8 text-center group hover:bg-white hover:shadow-xl transition-all">
                                <div>
                                    <i class="fa-light fa-camera-retro text-3xl text-primary mb-3"></i>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('news.starter_media') }}</div>
                                </div>
                            </div>
                            <div class="aspect-square bg-slate-50 rounded-2xl flex items-center justify-center border border-slate-100 p-8 text-center group hover:bg-white hover:shadow-xl transition-all">
                                <div>
                                    <i class="fa-light fa-microphone-stand text-3xl text-primary mb-3"></i>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('news.starter_interviews') }}</div>
                                </div>
                            </div>
                            <div class="aspect-square bg-slate-50 rounded-2xl flex items-center justify-center border border-slate-100 p-8 text-center group hover:bg-white hover:shadow-xl transition-all">
                                <div>
                                    <i class="fa-light fa-chart-line-up text-3xl text-primary mb-3"></i>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('news.starter_reports') }}</div>
                                </div>
                            </div>
                            <div class="aspect-square bg-slate-50 rounded-2xl flex items-center justify-center border border-slate-100 p-8 text-center group hover:bg-white hover:shadow-xl transition-all">
                                <div>
                                    <i class="fa-light fa-bullhorn text-3xl text-primary mb-3"></i>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('news.starter_updates') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
