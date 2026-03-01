@extends('layouts.member')

@section('content')
    <div class="container-fluid px-4 py-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black uppercase tracking-tight text-secondary">{{ __('member.notifications.title') }}</h1>
                <p class="text-slate-500">{{ __('member.notifications.subtitle') }}</p>
            </div>

            @if($notifications->where('read_at', null)->count() > 0)
                <form action="{{ route('member.notifications.markAllRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline py-2 px-4 text-xs">
                        {{ __('member.notifications.mark_all_read') }}
                    </button>
                </form>
            @endif
        </div>

        <div class="card overflow-hidden">
            @forelse($notifications as $notification)
                <div class="p-4 border-b border-slate-100 last:border-0 flex items-start justify-between gap-4 {{ $notification->unread() ? 'bg-primary/5' : '' }}">
                    <div class="flex gap-4">
                        <div class="shrink-0 mt-1">
                            @if(!empty($notification->data['user_avatar']))
                                <div class="relative">
                                    <img src="{{ $notification->data['user_avatar'] }}" alt="{{ $notification->data['user_name'] ?? '' }}" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm ring-1 ring-slate-100">
                                    <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-white rounded-full flex items-center justify-center shadow-sm border border-slate-100">
                                        @php
                                            $iconClass = match($notification->data['type'] ?? 'info') {
                                                'success' => 'fa-circle-check text-emerald-500',
                                                'warning' => 'fa-triangle-exclamation text-amber-500',
                                                'urgent' => 'fa-bolt text-red-500',
                                                default => 'fa-circle-info text-blue-500',
                                            };
                                        @endphp
                                        <i class="fa-light {{ $iconClass }} text-[10px]"></i>
                                    </div>
                                </div>
                            @else
                                @php
                                    $iconClass = match($notification->data['type'] ?? 'info') {
                                        'success' => 'fa-circle-check text-emerald-500',
                                        'warning' => 'fa-triangle-exclamation text-amber-500',
                                        'urgent' => 'fa-bolt text-red-500',
                                        default => 'fa-circle-info text-blue-500',
                                    };
                                @endphp
                                <div class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100 shadow-sm text-xl">
                                    <i class="fa-light {{ $iconClass }}"></i>
                                </div>
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-secondary leading-tight">
                                    @if(!empty($notification->data['user_name']))
                                        <span class="text-primary">{{ $notification->data['user_name'] }}:</span>
                                    @endif
                                    {{ $notification->data['title'] ?? __('member.notifications.default_title') }}
                                </h3>
                                @if($notification->unread())
                                    <span class="w-2 h-2 bg-primary rounded-full"></span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-600 mb-2">
                                {{ $notification->data['message'] ?? '' }}
                            </p>
                            <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>

                            @if(isset($notification->data['action_url']))
                                <a href="{{ $notification->data['action_url'] }}" class="inline-block mt-3 text-xs font-black uppercase tracking-widest text-primary hover:text-secondary transition-colors">
                                    {{ $notification->data['action_label'] ?? __('member.notifications.default_action_label') }} &rarr;
                                </a>
                            @endif
                        </div>
                    </div>

                    @if($notification->unread())
                        <form action="{{ route('member.notifications.markRead', $notification->id) }}" method="POST" class="shrink-0">
                            @csrf
                            <button type="submit" class="p-2 text-slate-400 hover:text-primary transition-colors" title="{{ __('member.notifications.mark_read') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-secondary mb-1">{{ __('member.notifications.no_notifications') }}</h3>
                    <p class="text-slate-500 text-sm">{{ __('member.notifications.no_notifications_text') }}</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="mt-8">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection
