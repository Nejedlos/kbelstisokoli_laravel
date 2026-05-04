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
                    {{-- Plakát / Upoutávka --}}
                    @if($event->hasMedia('poster'))
                        <div class="rounded-3xl overflow-hidden shadow-lg border border-slate-100 bg-white p-2">
                            <img src="{{ $event->getFirstMediaUrl('poster', 'large') }}"
                                 alt="{{ $event->getTranslation('title', app()->getLocale()) }}"
                                 class="w-full h-auto rounded-2xl">
                        </div>
                    @endif

                    {{-- Statistiky docházky --}}
                    @if($event->rsvp_enabled)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center gap-5 hover:shadow-md transition-shadow group">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                                    <i class="fa-light fa-circle-check"></i>
                                </div>
                                <div>
                                    <div class="text-2xl font-black text-secondary leading-none mb-1">{{ $stats['confirmed'] }}</div>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('events.stats_confirmed') }}</div>
                                </div>
                            </div>

                            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center gap-5 hover:shadow-md transition-shadow group">
                                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-xl group-hover:bg-rose-500 group-hover:text-white transition-colors">
                                    <i class="fa-light fa-circle-xmark"></i>
                                </div>
                                <div>
                                    <div class="text-2xl font-black text-secondary leading-none mb-1">{{ $stats['declined'] }}</div>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('events.stats_declined') }}</div>
                                </div>
                            </div>

                            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex items-center gap-5 hover:shadow-md transition-shadow group">
                                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl group-hover:bg-amber-500 group-hover:text-white transition-colors">
                                    <i class="fa-light fa-circle-question"></i>
                                </div>
                                <div>
                                    <div class="text-2xl font-black text-secondary leading-none mb-1">{{ $stats['maybe'] }}</div>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ __('events.stats_maybe') }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="bg-white rounded-3xl p-8 md:p-12 border border-slate-100 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-[0.03] pointer-events-none">
                            <i class="fa-light fa-calendar-check text-9xl"></i>
                        </div>
                        <div class="prose prose-slate max-w-none relative">
                            {!! \App\Support\TextProcessor::enhanceDescription($event->getTranslation('description', app()->getLocale()), $event->id) !!}
                        </div>
                    </div>

                    {{-- Přílohy --}}
                    @if($event->hasMedia('attachments'))
                        <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm">
                            <h3 class="text-lg font-black uppercase tracking-tight mb-6 text-secondary border-b border-slate-50 pb-4 flex items-center gap-3">
                                <i class="fa-light fa-file-lines text-primary"></i>
                                {{ __('events.attachments') }}
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($event->getMedia('attachments') as $media)
                                    <a href="{{ $media->getUrl() }}" target="_blank" class="flex items-center p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:border-primary/30 hover:bg-primary/5 transition-all group">
                                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center mr-4 shrink-0 shadow-sm group-hover:text-primary transition-colors">
                                            @php
                                                $icon = match(strtolower($media->extension)) {
                                                    'pdf' => 'fa-file-pdf',
                                                    'doc', 'docx' => 'fa-file-word',
                                                    'xls', 'xlsx' => 'fa-file-excel',
                                                    'ppt', 'pptx' => 'fa-file-powerpoint',
                                                    'jpg', 'jpeg', 'png', 'gif', 'webp' => 'fa-file-image',
                                                    'zip', 'rar', '7z' => 'fa-file-zipper',
                                                    default => 'fa-file'
                                                };
                                            @endphp
                                            <i class="fa-light {{ $icon }} text-lg"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-bold text-secondary truncate">{{ $media->name }}</div>
                                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">{{ $media->human_readable_size }} • {{ strtoupper($media->extension) }}</div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
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
