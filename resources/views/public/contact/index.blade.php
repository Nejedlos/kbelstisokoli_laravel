@extends('layouts.public')

@section('content')
    <x-page-header
        :title="__('contact.title')"
        :subtitle="__('contact.subtitle')"
        :breadcrumbs="[__('contact.breadcrumbs') => null]"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Contact Info -->
                <div class="lg:col-span-1 space-y-8">
                    <div>
                        <h2 class="text-3xl font-black uppercase tracking-tighter text-secondary mb-6">{{ __('contact.connect_with_us') }}</h2>
                        <p class="text-slate-600 mb-8 leading-relaxed">
                            {{ __('contact.connect_desc') }}
                        </p>
                    </div>

                    <div class="space-y-6">
                        {{-- Vedoucí týmu --}}
                        @if($branding['public_contact']['person'] ?? null)
                            <div class="p-6 bg-white rounded-2xl border border-slate-100 shadow-sm mb-8">
                                <h4 class="font-black uppercase tracking-widest text-[10px] text-primary mb-4">{{ __('contact.team_leader') ?? 'Vedoucí týmu' }}</h4>
                                <div class="space-y-4">
                                    <div class="font-bold text-secondary text-lg">{{ $branding['public_contact']['person'] }}</div>
                                    @if($branding['public_contact']['role'] ?? null)
                                        <div class="text-xs font-bold uppercase text-slate-400 -mt-3">{{ $branding['public_contact']['role'] }}</div>
                                    @endif

                                    <div class="pt-2 space-y-2">
                                        @if($branding['public_contact']['street'] ?? null)
                                            <div class="flex items-center text-sm text-slate-600">
                                                <i class="fa-light fa-location-dot w-5 text-primary opacity-70"></i>
                                                <span>{{ $branding['public_contact']['street'] }}, {{ $branding['public_contact']['city'] }}</span>
                                            </div>
                                        @endif
                                        @if($branding['public_contact']['phone'] ?? null)
                                            <div class="flex items-center text-sm text-slate-600">
                                                <i class="fa-light fa-phone w-5 text-primary opacity-70"></i>
                                                <a href="tel:{{ str_replace(' ', '', $branding['public_contact']['phone']) }}" class="hover:text-primary transition-colors">{{ $branding['public_contact']['phone'] }}</a>
                                            </div>
                                        @endif
                                        @if($branding['public_contact']['fax'] ?? null)
                                            <div class="flex items-center text-sm text-slate-600">
                                                <i class="fa-light fa-fax w-5 text-primary opacity-70"></i>
                                                <span>{{ $branding['public_contact']['fax'] }}</span>
                                            </div>
                                        @endif
                                        @if($branding['public_contact']['email'] ?? null)
                                            <div class="flex items-center text-sm text-slate-600">
                                                <i class="fa-light fa-envelope w-5 text-primary opacity-70"></i>
                                                <x-mailto :email="$branding['public_contact']['email']" class="hover:text-primary transition-colors" />
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($branding['venue']['name'] ?? null)
                            <div class="flex items-start group">
                                <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-primary shrink-0 mr-4 border border-slate-100 group-hover:bg-primary group-hover:text-white transition-all">
                                    <i class="fa-light fa-basketball-hoop text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-black uppercase tracking-widest text-[10px] text-slate-400 mb-1">{{ __('contact.venue') ?? 'Hala / Aréna' }}</h4>
                                    <div class="font-bold text-secondary tracking-tight">
                                        {{ $branding['venue']['name'] }}
                                    </div>
                                    <div class="text-sm text-slate-500">
                                        {{ $branding['venue']['street'] }}, {{ $branding['venue']['city'] }}
                                    </div>
                                    @if($branding['venue']['gps'] ?? null)
                                        <div class="text-[10px] font-mono text-slate-400 mt-1">
                                            GPS: {{ $branding['venue']['gps'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($branding['match_day'] ?? null)
                            <div class="flex items-start group">
                                <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-primary shrink-0 mr-4 border border-slate-100 group-hover:bg-primary group-hover:text-white transition-all">
                                    <i class="fa-light fa-calendar-star text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-black uppercase tracking-widest text-[10px] text-slate-400 mb-1">{{ __('contact.match_day') ?? 'Hlavní hrací den' }}</h4>
                                    <div class="font-bold text-secondary tracking-tight">
                                        {{ $branding['match_day'] }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($branding['contact']['address'] ?? null)
                            <div class="flex items-start group">
                                <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-primary shrink-0 mr-4 border border-slate-100 group-hover:bg-primary group-hover:text-white transition-all">
                                    <i class="fa-light fa-location-dot text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-black uppercase tracking-widest text-[10px] text-slate-400 mb-1">{{ __('contact.address') }}</h4>
                                    <address class="not-italic font-bold text-secondary tracking-tight">
                                        {!! nl2br(e($branding['contact']['address'])) !!}
                                    </address>
                                </div>
                            </div>
                        @endif

                        @if($branding['contact']['email'] ?? null)
                            <div class="flex items-start group">
                                <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-primary shrink-0 mr-4 border border-slate-100 group-hover:bg-primary group-hover:text-white transition-all">
                                    <i class="fa-light fa-envelope text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-black uppercase tracking-widest text-[10px] text-slate-400 mb-1">{{ __('contact.email') }}</h4>
                                    <x-mailto :email="$branding['contact']['email']" class="font-bold text-secondary tracking-tight hover:text-primary transition-colors" />
                                </div>
                            </div>
                        @endif

                        @if($branding['contact']['phone'] ?? null)
                            <div class="flex items-start group">
                                <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-primary shrink-0 mr-4 border border-slate-100 group-hover:bg-primary group-hover:text-white transition-all">
                                    <i class="fa-light fa-phone text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-black uppercase tracking-widest text-[10px] text-slate-400 mb-1">{{ __('contact.phone') }}</h4>
                                    <a href="tel:{{ str_replace(' ', '', $branding['contact']['phone']) }}" class="font-bold text-secondary tracking-tight hover:text-primary transition-colors">
                                        {{ $branding['contact']['phone'] }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Social Icons -->
                    <div class="pt-8 border-t border-slate-200">
                        <h4 class="font-black uppercase tracking-widest text-[10px] text-slate-400 mb-6">{{ __('contact.follow_us') }}</h4>
                        <div class="flex gap-4">
                            @if($branding['socials']['facebook'] ?? null)
                                <a href="{{ $branding['socials']['facebook'] }}"
                                   class="w-12 h-12 rounded-xl bg-secondary text-white flex items-center justify-center hover:bg-primary transition-all hover:-translate-y-1 shadow-md hover:shadow-primary/20"
                                   target="_blank"
                                   rel="noopener"
                                   aria-label="Facebook"
                                   data-track-click="social_link"
                                   data-track-label="Facebook"
                                   data-track-category="engagement">
                                    <i class="fa-brands fa-facebook-f"></i>
                                </a>
                            @endif
                            @if($branding['socials']['instagram'] ?? null)
                                <a href="{{ $branding['socials']['instagram'] }}"
                                   class="w-12 h-12 rounded-xl bg-secondary text-white flex items-center justify-center hover:bg-primary transition-all hover:-translate-y-1 shadow-md hover:shadow-primary/20"
                                   target="_blank"
                                   rel="noopener"
                                   aria-label="Instagram"
                                   data-track-click="social_link"
                                   data-track-label="Instagram"
                                   data-track-category="engagement">
                                    <i class="fa-brands fa-instagram"></i>
                                </a>
                            @endif
                            @if($branding['socials']['youtube'] ?? null)
                                <a href="{{ $branding['socials']['youtube'] }}"
                                   class="w-12 h-12 rounded-xl bg-secondary text-white flex items-center justify-center hover:bg-primary transition-all hover:-translate-y-1 shadow-md hover:shadow-primary/20"
                                   target="_blank"
                                   rel="noopener"
                                   aria-label="YouTube"
                                   data-track-click="social_link"
                                   data-track-label="YouTube"
                                   data-track-category="engagement">
                                    <i class="fa-brands fa-youtube"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Map & Form Column -->
                <div class="lg:col-span-2 space-y-12">
                    <!-- Interactive Map -->
                    <div class="card h-[400px] bg-slate-100 relative overflow-hidden border-2 border-slate-200">
                        @if($branding['venue']['map_url'] ?? null)
                            <iframe src="{{ $branding['venue']['map_url'] }}"
                                    class="absolute inset-0 w-full h-full"
                                    style="border:0;"
                                    allowfullscreen=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    aria-label="{{ app()->getLocale() === 'cs' ? 'Mapa haly' : 'Gym map' }} – {{ $branding['venue']['name'] ?? '' }}">
                            </iframe>
                        @else
                            <div class="flex items-center justify-center h-full text-slate-400 italic">
                                {{ __('contact.map_not_available') ?? 'Mapa není k dispozici' }}
                            </div>
                        @endif
                    </div>

                    <!-- Simple Contact Info / CTA -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="bg-primary text-white p-10 rounded-[2.5rem] shadow-xl shadow-primary/20 relative overflow-hidden group">
                            <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-white/10 rounded-full group-hover:scale-110 transition-transform duration-700"></div>
                            <i class="fa-light fa-basketball fa-4xl absolute -top-4 -right-4 opacity-10 rotate-12"></i>

                            <h3 class="text-2xl font-black uppercase tracking-tighter mb-4">{{ __('contact.want_to_play') }}</h3>
                            <p class="text-white/80 mb-8 leading-relaxed">{{ __('contact.want_to_play_desc') }}</p>
                            <a href="{{ route('public.recruitment.index') }}" class="btn bg-white text-primary hover:bg-secondary hover:text-white px-8">
                                {{ __('contact.more_info') }}
                            </a>
                        </div>

                        <div class="bg-secondary text-white p-10 rounded-[2.5rem] shadow-xl shadow-secondary/20 relative overflow-hidden group border border-white/5">
                            <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-white/5 rounded-full group-hover:scale-110 transition-transform duration-700"></div>
                            <i class="fa-light fa-envelope-open-text fa-4xl absolute -top-4 -right-4 opacity-5 rotate-12"></i>

                            <h3 class="text-2xl font-black uppercase tracking-tighter mb-4">{{ __('contact.write_us') }}</h3>
                            <p class="text-white/80 mb-8 leading-relaxed">{{ __('contact.write_us_desc') }}</p>
                            <x-mailto :email="$branding['contact']['email'] ?? ''" class="btn btn-primary px-8">
                                {{ __('contact.send_email') }}
                            </x-mailto>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
