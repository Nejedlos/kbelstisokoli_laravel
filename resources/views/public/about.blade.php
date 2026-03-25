@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('about.title')"
        :subtitle="__('about.subtitle')"
        :breadcrumbs="[__('about.breadcrumbs') => null]"
        image="assets/img/hero/hero-about.webp"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            <div class="max-w-4xl mx-auto space-y-16">
                {{-- O klubu --}}
                <div class="prose prose-slate prose-lg max-w-none">
                    <h2 class="text-3xl font-black uppercase tracking-tight text-secondary mb-6">{{ __('about.club_info_title') }}</h2>
                    <div class="text-slate-600 leading-relaxed">
                        @foreach(__('about.club_info_paragraphs') as $paragraph)
                            <p class="mb-4">{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </div>

                {{-- Mise a Vize --}}
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100">
                        <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center text-primary mb-6">
                            <i class="fa-light fa-bullseye-arrow text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-black uppercase tracking-tight text-secondary mb-4">{{ __('about.mission_title') }}</h3>
                        <p class="text-slate-500 leading-relaxed">{{ __('about.mission_text') }}</p>
                    </div>
                    <div class="bg-secondary text-white p-8 rounded-[2.5rem] shadow-xl shadow-secondary/20 border border-white/5">
                        <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-primary mb-6">
                            <i class="fa-light fa-eye text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-black uppercase tracking-tight text-white mb-4">{{ __('about.vision_title') }}</h3>
                        <p class="text-white/70 leading-relaxed">{{ __('about.vision_text') }}</p>
                    </div>
                </div>

                {{-- CTA na historii --}}
                <div class="bg-slate-900 text-white rounded-[3rem] p-8 md:p-16 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-1/2 h-full bg-primary/5 -skew-x-12 translate-x-1/3 pointer-events-none"></div>
                    <i class="fa-light fa-hourglass-clock text-[10rem] absolute -bottom-8 -right-8 opacity-5 -rotate-12 group-hover:rotate-0 transition-transform duration-1000"></i>

                    <div class="relative z-10 max-w-2xl">
                        <h3 class="text-3xl md:text-4xl font-black uppercase tracking-tight mb-6">{{ __('about.history_cta_title') }}</h3>
                        <p class="text-white/60 text-lg mb-10 leading-relaxed">{{ __('about.history_cta_text') }}</p>
                        <a href="{{ route('public.history.index') }}" class="btn btn-primary btn-glow px-10">
                            {{ __('about.history_cta_button') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
