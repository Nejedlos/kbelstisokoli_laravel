<div x-data="{ open: false }" class="relative">
    <button @click="open = !open"
            class="flex hover:bg-primary/5 hover:text-primary items-center justify-center min-h-[44px] min-w-[44px] p-2.5 relative rounded-xl text-slate-400 transition-all focus:outline-none"
            :class="{ 'bg-primary/5 text-primary': open }">
        <i class="fa-light fa-bell text-xl"></i>
        @if($unreadCount > 0)
            <span class="absolute animate-pulse bg-primary border-2 border-white flex font-black h-4.5 items-center justify-center right-2.5 rounded-full shadow-sm text-[9px] text-white top-2.5 w-4.5">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="fixed inset-x-0 top-[70px] sm:absolute sm:inset-auto sm:top-full sm:right-0 sm:mt-3 w-full sm:w-[300px] bg-white rounded-none sm:rounded-2xl shadow-xl border-y sm:border border-slate-100 z-50 overflow-hidden"
         style="display: none;">

        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-900">{{ __('member.notifications.title') }}</span>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" @click="open = false" class="text-[9px] font-black uppercase tracking-widest text-primary hover:text-secondary transition-colors">
                    {{ __('member.notifications.mark_all_read') }}
                </button>
            @endif
        </div>

        <div class="max-h-[60vh] sm:max-h-[480px] overflow-y-auto custom-scrollbar" wire:poll.30s>
            @forelse($latestNotifications as $notification)
                <a href="{{ route('member.notifications.redirect', $notification->id) }}"
                   class="block p-4 border-b border-slate-50 last:border-0 hover:bg-slate-50/80 transition-all {{ $notification->unread() ? 'bg-primary/5' : '' }}">
                    <div class="flex gap-3">
                        <div class="shrink-0 mt-0.5">
                            @if(!empty($notification->data['user_avatar']))
                                <div class="relative">
                                    <img src="{{ $notification->data['user_avatar'] }}" alt="{{ $notification->data['user_name'] ?? '' }}" class="w-8 h-8 rounded-full object-cover border-2 border-white shadow-sm ring-1 ring-slate-100">
                                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-white rounded-full flex items-center justify-center shadow-sm border border-slate-100">
                                        @php
                                            $iconClass = match($notification->data['type'] ?? 'info') {
                                                'success' => 'fa-circle-check text-emerald-500',
                                                'warning' => 'fa-triangle-exclamation text-amber-500',
                                                'urgent' => 'fa-bolt text-red-500',
                                                default => 'fa-circle-info text-blue-500',
                                            };
                                        @endphp
                                        <i class="fa-light {{ $iconClass }} text-[8px]"></i>
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
                                <div class="w-8 h-8 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100 shadow-sm">
                                    <i class="fa-light {{ $iconClass }} text-lg"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <h4 class="text-[12px] font-black text-secondary leading-tight truncate">
                                    @if(!empty($notification->data['user_name']))
                                        <span class="text-primary">{{ $notification->data['user_name'] }}:</span>
                                    @endif
                                    {{ $notification->data['title'] ?? __('member.notifications.default_title') }}
                                </h4>
                                @if($notification->unread())
                                    <span class="shrink-0 w-1.5 h-1.5 bg-primary rounded-full shadow-[0_0_5px_rgba(225,29,72,0.5)]"></span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500 leading-snug line-clamp-2">
                                {{ $notification->data['message'] ?? '' }}
                            </p>
                            <div class="mt-1.5 text-[9px] font-bold uppercase tracking-widest text-slate-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center">
                    <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fa-light fa-bell-slash text-slate-300"></i>
                    </div>
                    <p class="text-[11px] text-slate-400 font-bold tracking-wide uppercase">{{ __('member.notifications.no_notifications') }}</p>
                </div>
            @endforelse
        </div>

        <div class="p-2 border-t border-slate-100 bg-slate-50/30">
            <a href="{{ route('member.notifications.index') }}"
               class="flex items-center justify-center w-full py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-primary hover:bg-white border border-transparent hover:border-slate-100 transition-all">
                {{ __('member.notifications.view_all') }}
                <i class="fa-light fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</div>
