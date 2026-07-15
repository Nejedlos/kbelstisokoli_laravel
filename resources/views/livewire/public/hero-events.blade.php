<div wire:poll.60s>
    @if($upcomingEvents->isNotEmpty())
        <div class="mb-10 {{ $alignment === 'center' ? 'text-center' : ($alignment === 'right' ? 'text-right' : 'text-left') }}">
            <div class="inline-flex items-center gap-2 mb-4">
                <span class="w-8 h-px bg-primary/40"></span>
                <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-primary/80">
                    {{ __('general.upcoming_events_title') }}
                </h4>
                <span class="w-8 h-px bg-primary/40"></span>
            </div>

            <div class="flex flex-col gap-4 max-w-2xl {{ $alignment === 'center' ? 'mx-auto' : ($alignment === 'right' ? 'ml-auto' : '') }}">
                @foreach($upcomingEvents as $event)
                    <a href="{{ $event['url'] }}" class="group/event relative flex items-center gap-5 bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl p-5 transition-all duration-500 hover:bg-white/10 hover:border-primary/40 hover:-translate-y-1.5 shadow-2xl overflow-hidden">
                        {{-- Background glow on hover --}}
                        <div class="absolute -inset-1 bg-gradient-to-r from-primary/0 via-primary/5 to-primary/0 opacity-0 group-hover/event:opacity-100 transition-opacity duration-700 blur-xl pointer-events-none"></div>

                        {{-- Date Box --}}
                        <div class="relative flex flex-col items-center justify-center w-16 h-16 shrink-0 rounded-2xl bg-white/5 text-white border border-white/10 group-hover/event:bg-primary group-hover/event:text-white group-hover/event:border-primary transition-all duration-500 shadow-lg">
                            <span class="text-[11px] font-black uppercase leading-none mb-1.5 opacity-60 group-hover/event:opacity-100 tracking-wider">{{ $event['date']->translatedFormat('D') }}</span>
                            <span class="text-xl font-black leading-none">{{ $event['date']->format('d.m.') }}</span>
                        </div>

                        {{-- Content --}}
                        <div class="relative flex flex-col min-w-0 pr-4 sm:pr-8">
                            <div class="flex items-center gap-3 mb-1.5">
                                <span class="text-[11px] font-black uppercase tracking-widest text-primary">{{ $event['team_short'] }}</span>
                                <span class="w-1 h-1 rounded-full bg-white/20"></span>
                                @if($event['date']->isToday())
                                    <div class="flex items-center gap-1.5">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                                        </span>
                                        <span class="text-[11px] font-black uppercase text-primary animate-pulse">{{ __('general.today') }}</span>
                                    </div>
                                @else
                                    <span class="text-[11px] font-bold text-white/50 group-hover/event:text-white/80 transition-colors">{{ $event['date']->format('H:i') }}</span>
                                @endif

                                @if(($event['confirmed_count'] ?? 0) > 0)
                                    <span class="w-1 h-1 rounded-full bg-white/20"></span>
                                    <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-primary/10 border border-primary/20 group-hover/event:bg-primary/20 transition-colors">
                                        <i class="fa-light fa-user-check text-[10px] text-primary"></i>
                                        <span class="text-[10px] font-black uppercase tracking-wider text-white/90">
                                            {{ __('general.confirmed_count', ['count' => $event['confirmed_count']]) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <h3 class="text-base md:text-xl font-black text-white group-hover/event:text-primary transition-colors mb-1 leading-tight">
                                @if($event['type'] === 'match')
                                    <i class="fa-light fa-basketball mr-2 opacity-60 group-hover/event:rotate-[15deg] transition-transform duration-500 inline-block"></i>
                                    <span class="italic">{{ $event['title'] }}</span>
                                @elseif($event['type'] === 'training')
                                    @if($event['sport'] === 'volleyball')
                                        <i class="fa-light fa-volleyball mr-2 opacity-60 group-hover/event:rotate-[15deg] transition-transform duration-500 inline-block"></i>
                                    @else
                                        <i class="fa-light fa-basketball mr-2 opacity-60 group-hover/event:rotate-[15deg] transition-transform duration-500 inline-block"></i>
                                    @endif
                                    {{ $event['title'] }}
                                @else
                                    <i class="fa-light fa-calendar-star mr-2 opacity-60 group-hover/event:scale-110 transition-transform duration-500 inline-block"></i>
                                    {{ $event['title'] }}
                                @endif
                            </h3>
                            @if($event['location'])
                                <div class="flex items-center gap-1.5 text-[11px] text-white/40 group-hover/event:text-white/60 transition-colors">
                                    <i class="fa-light fa-location-dot shrink-0"></i>
                                    <span class="line-clamp-1">{{ $event['location'] }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Arrow --}}
                        <div class="absolute right-6 top-1/2 -translate-y-1/2 opacity-0 -translate-x-4 group-hover/event:opacity-100 group-hover/event:translate-x-0 transition-all duration-500 text-primary hidden md:block">
                            <i class="fa-light fa-arrow-right text-2xl"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
