<x-filament-panels::page>
    @if($selectedEventId && $selectedEventType)
        <div class="space-y-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <button
                        wire:click="resetSelection"
                        class="p-2 -ml-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-all"
                        title="Zpět na seznam událostí"
                    >
                        <i class="fa-light fa-arrow-left text-xl"></i>
                    </button>

                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 leading-tight">
                            @php
                                $event = $this->selected_event;
                                $title = 'Událost';
                                if ($event instanceof \App\Models\BasketballMatch) {
                                    $title = $event->getOfficialTeamNameAttribute() . ' vs ' . $event->getOfficialOpponentNameAttribute();
                                } elseif ($event instanceof \App\Models\Training) {
                                    $title = 'Trénink' . ($event->location ? ' - ' . $event->location : '');
                                } elseif ($event instanceof \App\Models\ClubEvent) {
                                    $title = $event->getTranslation('title', 'cs');
                                }
                            @endphp
                            {{ $title }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-sm text-gray-500">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-light fa-calendar text-primary-500"></i>
                                {{ $this->selected_event?->starts_at->format('d.m.Y H:i') }}
                            </span>
                            @if($this->selected_event?->location)
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-light fa-location-dot text-primary-500"></i>
                                    {{ $this->selected_event->location }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="fi-section fi-section-border rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                @livewire('admin.attendance-tracker', [
                    'attendableId' => $selectedEventId,
                    'attendableType' => $selectedEventType
                ], key('tracker-'.$selectedEventType.'-'.$selectedEventId))
            </div>
        </div>
    @else
        <div class="space-y-4">
            <div class="grid grid-cols-1 gap-4">
                @forelse($this->events as $event)
                    @php
                        $isTraining = $event['type'] === \App\Models\Training::class;
                        $isMatch = $event['type'] === \App\Models\BasketballMatch::class;
                        $isClubEvent = $event['type'] === \App\Models\ClubEvent::class;

                        $bgColor = 'bg-white';
                        $hoverBorder = 'hover:border-primary-500';
                        $iconBg = 'bg-gray-50';
                        $iconColor = 'text-gray-400';

                        if ($isTraining) {
                            $bgColor = 'bg-gradient-to-br from-white to-blue-50/30';
                            $iconBg = 'bg-blue-50';
                            $iconColor = 'text-blue-600';
                            $hoverBorder = 'hover:border-blue-400';
                        } elseif ($isMatch) {
                            $bgColor = 'bg-gradient-to-br from-white to-orange-50/30';
                            $iconBg = 'bg-orange-50';
                            $iconColor = 'text-orange-600';
                            $hoverBorder = 'hover:border-orange-400';
                        } elseif ($isClubEvent) {
                            $bgColor = 'bg-gradient-to-br from-white to-purple-50/30';
                            $iconBg = 'bg-purple-50';
                            $iconColor = 'text-purple-600';
                            $hoverBorder = 'hover:border-purple-400';
                        }
                    @endphp
                    <div
                        wire:click="selectEvent('{{ $event['id'] }}', '{{ addslashes($event['type']) }}')"
                        class="group relative flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 {{ $bgColor }} dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm {{ $hoverBorder }} dark:hover:border-primary-500 cursor-pointer transition-all active:scale-[0.98] hover:shadow-md"
                    >
                        <div class="flex-shrink-0 flex items-center justify-center w-14 h-14 rounded-xl {{ $iconBg }} dark:bg-gray-800 {{ $iconColor }} group-hover:scale-110 transition-transform duration-300 shadow-sm">
                            <span class="text-2xl">
                                {!! $event['icon'] !!}
                            </span>
                        </div>

                        <div class="flex-grow min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">
                                    {{ (new \ReflectionClass($event['type']))->getShortName() }}
                                </span>
                                @foreach($event['teams'] as $team)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-white/50 dark:bg-primary-900/30 text-gray-600 dark:text-primary-300 border border-gray-200 dark:border-primary-800">
                                        {{ $team }}
                                    </span>
                                @endforeach
                            </div>
                            <h3 class="font-bold text-gray-900 dark:text-gray-100 group-hover:text-primary-600 transition-colors truncate text-base mb-1">
                                {{ $event['title'] }}
                            </h3>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500">
                                <span class="flex items-center gap-1">
                                    <i class="fa-light fa-clock text-primary-500/70"></i>
                                    {{ $event['starts_at']->format('H:i') }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <i class="fa-light fa-calendar-day text-primary-500/70"></i>
                                    {{ $event['starts_at']->format('d.m.Y') }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-6 pr-2">
                            <div class="flex gap-4 text-center">
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-400 uppercase font-bold tracking-tighter">Účast</span>
                                    <span class="text-lg font-black {{ $event['stats']['attended'] > 0 ? 'text-success-600' : 'text-gray-400' }}">
                                        {{ $event['stats']['attended'] }}<span class="text-xs text-gray-300 font-normal mx-0.5">/</span>{{ $event['stats']['expected'] }}
                                    </span>
                                </div>
                                <div class="flex flex-col border-l border-gray-100 dark:border-gray-800 pl-4">
                                    <span class="text-xs text-gray-400 uppercase font-bold tracking-tighter">Omluvy</span>
                                    <span class="text-lg font-black {{ $event['stats']['excused'] > 0 ? 'text-warning-600' : 'text-gray-400' }}">
                                        {{ $event['stats']['excused'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="hidden sm:block ml-2">
                                 <i class="fa-light fa-chevron-right text-gray-300 group-hover:text-primary-400 transition-all group-hover:translate-x-1"></i>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center bg-gray-50 dark:bg-gray-800/50 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800">
                        <i class="fa-light fa-calendar-xmark text-4xl text-gray-400 mb-4 block"></i>
                        <p class="text-gray-500 font-medium">Žádné nedávné události k zapsání docházky nebyly nalezeny.</p>
                        <p class="text-sm text-gray-400 mt-1">Zobrazujeme události 7 dní zpět a 2 dny dopředu.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    <style>
        .animate-in {
            animation-duration: 0.3s;
            animation-fill-mode: both;
        }
        .fade-in {
            animation-name: fadeIn;
        }
        .zoom-in {
            animation-name: zoomIn;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</x-filament-panels::page>
