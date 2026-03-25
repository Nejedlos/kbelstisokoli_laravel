@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('gdpr.title')"
        :subtitle="__('gdpr.subtitle')"
        :breadcrumbs="[__('gdpr.breadcrumbs') => null]"
        image="assets/img/home/basketball-court-detail.jpg"
        alignment="left"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            <div class="max-w-4xl mx-auto space-y-16">

                {{-- Úvodní text --}}
                <div class="prose prose-slate prose-lg max-w-none">
                    <h2 class="text-3xl font-black uppercase tracking-tight text-secondary mb-6">{{ __('gdpr.intro_title') }}</h2>
                    <div class="text-slate-600 leading-relaxed">
                        {!! __('gdpr.intro_content') !!}
                    </div>
                </div>

                {{-- Karty s údaji --}}
                <div>
                    <h3 class="text-2xl font-black uppercase tracking-tight text-secondary mb-8 text-center">{{ __('gdpr.roster_title') }}</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        @foreach(__('gdpr.cards') as $card)
                            <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                                <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center text-primary mb-6">
                                    <i class="fa-light fa-{{ $card['icon'] }} text-2xl"></i>
                                </div>
                                <h4 class="text-xl font-bold text-secondary mb-3">{{ $card['title'] }}</h4>
                                <p class="text-slate-500 leading-relaxed text-sm">{{ $card['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Práva uživatele --}}
                <div class="bg-white rounded-[2.5rem] p-8 md:p-12 border border-slate-100 shadow-xl shadow-slate-200/50">
                    <div class="flex flex-col md:flex-row gap-8 items-center">
                        <div class="w-20 h-20 bg-secondary text-white rounded-3xl flex items-center justify-center shrink-0">
                            <i class="fa-light fa-gavel text-3xl"></i>
                        </div>
                        <div class="flex-1 text-center md:text-left">
                            <h3 class="text-2xl font-black uppercase tracking-tight text-secondary mb-4">{{ __('gdpr.rights_title') }}</h3>
                            <p class="text-slate-600 leading-relaxed mb-6">{{ __('gdpr.rights_text') }}</p>
                            <a href="{{ route('public.contact.index') }}" class="btn btn-outline-primary">
                                {{ __('gdpr.rights_button') }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Detailní právní info --}}
                <div class="prose prose-slate prose-sm max-w-none bg-slate-50 p-8 md:p-12 rounded-[2rem] border border-slate-200">
                    {!! __('gdpr.legal_content') !!}
                </div>

            </div>
        </div>
    </div>
@endsection
