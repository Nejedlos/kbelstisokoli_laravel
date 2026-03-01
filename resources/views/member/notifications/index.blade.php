@extends('layouts.member')

@section('content')
    <div class="container-fluid px-4 py-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black uppercase tracking-tight text-secondary leading-none">{{ __('member.notifications.title') }}</h1>
                <p class="text-slate-500 mt-2 font-medium">{{ __('member.notifications.subtitle') }}</p>
            </div>

            @if($notifications->where('read_at', null)->count() > 0)
                <form action="{{ route('member.notifications.markAllRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary px-6 shadow-lg shadow-primary/20">
                        <i class="fa-light fa-check-double mr-2"></i> {{ __('member.notifications.mark_all_read') }}
                    </button>
                </form>
            @endif
        </div>

        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            @forelse($notifications as $notification)
                @php
                    $isUnread = $notification->unread();
                    $actionUrl = isset($notification->data['action_url']) ? route('member.notifications.redirect', $notification->id) : null;
                @endphp
                <div class="relative group border-b border-slate-50 last:border-0 transition-all duration-300 {{ $isUnread ? 'bg-primary/5 hover:bg-primary/[0.08]' : 'hover:bg-slate-50/80' }}">
                    <div class="flex items-center gap-4 p-5 sm:p-6">
                        @if($actionUrl)
                            <a href="{{ $actionUrl }}" class="absolute inset-0 z-20" aria-label="{{ $notification->data['title'] ?? '' }}"></a>
                        @endif

                        <div class="shrink-0">
                            @if(!empty($notification->data['user_avatar']))
                                <div class="relative">
                                    <img src="{{ $notification->data['user_avatar'] }}" alt="{{ $notification->data['user_name'] ?? '' }}" class="w-12 h-12 rounded-2xl object-cover border-2 border-white shadow-md ring-1 ring-slate-100">
                                    <div class="absolute -bottom-1.5 -right-1.5 w-6 h-6 bg-white rounded-full flex items-center justify-center shadow-md border border-slate-100">
                                        @php
                                            $iconClass = match($notification->data['type'] ?? 'info') {
                                                'success' => 'fa-circle-check text-emerald-500',
                                                'warning' => 'fa-triangle-exclamation text-amber-500',
                                                'urgent' => 'fa-bolt text-red-500',
                                                default => 'fa-circle-info text-blue-500',
                                            };
                                        @endphp
                                        <i class="fa-light {{ $iconClass }} text-[11px]"></i>
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
                                <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center border border-slate-100 shadow-sm text-2xl text-slate-400">
                                    <i class="fa-light {{ $iconClass }}"></i>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mb-1">
                                <h3 class="font-black text-secondary leading-tight text-base flex items-center gap-2">
                                    @if(!empty($notification->data['user_name']))
                                        <span class="text-primary">{{ $notification->data['user_name'] }}:</span>
                                    @endif
                                    {{ !empty($notification->data['title']) ? __($notification->data['title']) : __('member.notifications.default_title') }}
                                </h3>
                                @if($isUnread)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-primary text-white shadow-sm shadow-primary/30">
                                        {{ __('member.notifications.new') }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-500 font-medium leading-relaxed max-w-3xl">
                                {{ $notification->data['message'] ?? '' }}
                            </p>
                            <div class="mt-2 flex items-center gap-3">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 flex items-center">
                                    <i class="fa-light fa-clock mr-1.5"></i> {{ $notification->created_at->diffForHumans() }}
                                </span>
                                @if($actionUrl)
                                    <span class="text-[10px] font-black uppercase tracking-widest text-primary group-hover:translate-x-1 transition-transform inline-flex items-center">
                                        {{ !empty($notification->data['action_label']) ? __($notification->data['action_label']) : __('member.notifications.default_action_label') }} <i class="fa-light fa-arrow-right ml-1.5"></i>
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($isUnread)
                            <div class="shrink-0 ml-auto relative z-30">
                                <form action="{{ route('member.notifications.markRead', $notification->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-10 h-10 rounded-xl bg-white border border-slate-100 text-slate-400 hover:text-primary hover:border-primary/30 hover:shadow-lg hover:shadow-primary/10 transition-all flex items-center justify-center relative z-40" title="{{ __('member.notifications.mark_read') }}">
                                        <i class="fa-light fa-check text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        @else
                             <div class="shrink-0 ml-auto opacity-30 relative z-30">
                                <div class="w-10 h-10 flex items-center justify-center">
                                    <i class="fa-light fa-circle-check text-slate-300 text-xl"></i>
                                </div>
                             </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-16 text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-slate-100 shadow-inner">
                        <i class="fa-light fa-bell-slash text-slate-200 text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-black text-secondary mb-2 uppercase tracking-tight">{{ __('member.notifications.no_notifications') }}</h3>
                    <p class="text-slate-500 font-medium max-w-sm mx-auto leading-relaxed">{{ __('member.notifications.no_notifications_text') }}</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="mt-10">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection
