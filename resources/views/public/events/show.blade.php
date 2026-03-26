@extends('layouts.public')

@section('content')
    <x-page-header
        :title="$event->getTranslation('title', app()->getLocale())"
        :subtitle="__('events.type_' . $event->event_type)"
        :breadcrumbs="[__('events.breadcrumbs') => route('public.events.index'), $event->getTranslation('title', app()->getLocale()) => null]"
        image="assets/img/hero/hero-events.webp"
    />

    <div class="section-padding bg-bg">
        <div class="container">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white rounded-3xl p-8 md:p-12 border border-slate-100 shadow-sm">
                        <div class="prose prose-slate max-w-none">
                            {!! \App\Support\TextProcessor::enhanceDescription($event->getTranslation('description', app()->getLocale()), $event->id) !!}
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm">
                        <h3 class="text-lg font-black uppercase tracking-tight mb-6 text-secondary border-b border-slate-50 pb-4">
                            {{ __('general.details') }}
                        </h3>

                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                                    <i class="fa-light fa-calendar text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">{{ __('general.date') }}</div>
                                    <div class="font-bold text-secondary">
                                        {{ $event->starts_at->translatedFormat('j. F Y') }}
                                        @if($event->ends_at && !$event->starts_at->isSameDay($event->ends_at))
                                            — {{ $event->ends_at->translatedFormat('j. F Y') }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                                    <i class="fa-light fa-clock text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">{{ __('general.time') }}</div>
                                    <div class="font-bold text-secondary">
                                        {{ $event->starts_at->format(__('general.time_format')) }}
                                        @if($event->ends_at)
                                            — {{ $event->ends_at->format(__('general.time_format')) }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($event->location)
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                                    <i class="fa-light fa-location-dot text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">{{ __('general.location') }}</div>
                                    <div class="font-bold text-secondary">{{ $event->location }}</div>
                                </div>
                            </div>
                            @endif

                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                                    <i class="fa-light fa-users text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">{{ __('general.teams') }}</div>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @forelse($event->teams as $team)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600">
                                                {{ $team->name }}
                                            </span>
                                        @empty
                                            <span class="text-slate-500 font-medium">{{ __('general.all_teams') }}</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($event->rsvp_enabled && ($event->ends_at ?? $event->starts_at)->isFuture())
                        <div class="mt-8 pt-8 border-t border-slate-50">
                            <a href="{{ route('member.attendance.show', ['type' => 'event', 'id' => $event->id]) }}" class="btn btn-primary w-full py-4 shadow-lg shadow-primary/20">
                                <i class="fa-light fa-user-check mr-2"></i>
                                {{ auth()->check() ? __('events.rsvp_go_to_attendance') : __('events.rsvp_login') }}
                            </a>
                            @guest
                            <p class="text-[10px] text-center text-slate-400 mt-3 font-medium uppercase tracking-wide">
                                {{ __('events.rsvp_notice') }}
                            </p>
                            @endguest
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
