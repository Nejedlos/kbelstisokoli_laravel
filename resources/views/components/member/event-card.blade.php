@props(['event', 'showActions' => true])

@php
    $type = $event['type'];
    $data = $event['data'];
    $time = $event['time'];
    $isPast = $time->isPast();
    $canChange = $time->isAfter(now()->addMinutes(90));
    $attendance = $data->attendances->first();
    $status = $attendance?->planned_status ?? 'pending';

    $confirmedCount = $data->confirmed_count ?? 0;
    $declinedCount = $data->declined_count ?? 0;
    $maybeCount = $data->maybe_count ?? 0;
    $totalCount = $data->expected_players_count ?? 0;
    $pendingCount = max(0, $totalCount - ($confirmedCount + $declinedCount + $maybeCount));

    $typeIcons = [
        'training' => [
            'basketball' => 'basketball',
            'volleyball' => 'volleyball',
            'default' => 'dumbbell',
        ],
        'match' => 'basketball',
        'event' => 'star',
    ];

    $icon = $typeIcons[$type];
    if ($type === 'training') {
        $icon = $typeIcons['training'][$data->sport ?? 'default'] ?? $typeIcons['training']['default'];
    }

    $typeLabels = [
        'training' => [
            'basketball' => __('member.attendance.event_types.training_basketball'),
            'volleyball' => __('member.attendance.event_types.training_volleyball'),
            'default' => __('member.attendance.event_types.training'),
        ],
        'match' => __('member.attendance.event_types.match'),
        'event' => __('member.attendance.event_types.event'),
    ];

    $label = $typeLabels[$type];
    if ($type === 'training') {
        $label = $typeLabels['training'][$data->sport ?? 'default'] ?? $typeLabels['training']['default'];
    }

    $statusColors = [
        'pending' => 'bg-slate-100 text-slate-600',
        'confirmed' => 'bg-success-100 text-success-700',
        'declined' => 'bg-danger-100 text-danger-700',
        'maybe' => 'bg-warning-100 text-warning-700',
    ];

    $statusLabels = [
        'pending' => __('member.attendance.status.pending'),
        'confirmed' => __('member.attendance.status.confirmed'),
        'declined' => __('member.attendance.status.declined'),
        'maybe' => __('member.attendance.status.maybe'),
    ];
@endphp

<div class="relative group" id="event-card-{{ $type }}-{{ $data->id }}" x-data="{ predictionOpen: false }" x-effect="
    predictionOpen ? document.body.setAttribute('data-prediction-open', 'true') : document.body.removeAttribute('data-prediction-open');
    const fab = document.querySelector('.ks-fab-trigger');
    if (fab) {
        const isMobile = window.innerWidth < 768;
        if (isMobile && (predictionOpen || document.body.hasAttribute('data-batch-active'))) {
            fab.style.setProperty('display', 'none', 'important');
        } else {
            fab.style.setProperty('display', 'flex', 'important');
        }
    }
">
    <div class="absolute inset-0 bg-white rounded-3xl border border-slate-200/60 shadow-lg shadow-slate-200/20 group-hover:shadow-xl group-hover:shadow-primary/5 group-hover:border-primary/20 transition-all duration-500 overflow-hidden"
         :class="selectedEvents.includes('{{ $type . ':' . $data->id }}') ? 'ring-2 ring-primary ring-offset-2 border-primary bg-primary/[0.02] shadow-primary/10' : ''">
        <div class="absolute top-0 left-0 w-1 h-full js-event-status-strip bg-{{ $status === 'confirmed' ? 'emerald-500' : ($status === 'declined' ? 'rose-500' : 'slate-200') }}"
             :class="selectedEvents.includes('{{ $type . ':' . $data->id }}') ? 'w-2 bg-primary' : ''"></div>
    </div>

    <div class="relative p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="flex items-center gap-4 sm:gap-5 flex-1 w-full min-w-0">
            @if($showActions && $canChange && !($type === 'match' && $data->has_score))
                <div class="shrink-0 z-20">
                    <label class="relative inline-flex items-center cursor-pointer group/cb">
                        <input type="checkbox"
                               x-model="selectedEvents"
                               value="{{ $type . ':' . $data->id }}"
                               class="peer sr-only">
                        <div class="w-7 h-7 bg-white/80 backdrop-blur shadow-sm border-2 border-slate-200 rounded-xl peer-checked:bg-primary peer-checked:border-primary transition-all duration-300 flex items-center justify-center group-hover/cb:border-primary/50 group-hover/cb:scale-110">
                            <i class="fa-solid fa-check text-white text-[12px] opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                        </div>
                    </label>
                </div>
            @endif

            <a href="{{ route('member.attendance.show', ['type' => $type, 'id' => $data->id]) }}" class="flex items-center gap-4 sm:gap-5 flex-1 min-w-0 transition-all duration-500">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-slate-50 flex flex-col items-center justify-center text-secondary leading-none border border-slate-100 shrink-0 group-hover:bg-white group-hover:shadow-md transition-all duration-500">
                <span class="text-[8px] sm:text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">{{ $time->translatedFormat('M') }}</span>
                <span class="text-lg sm:text-xl font-black tracking-tight">{{ $time->format('d') }}</span>
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2 sm:gap-4 mb-1 sm:mb-1.5">
                    <span class="text-[8px] sm:text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-1.5 group-hover:text-primary transition-colors">
                        <i class="fa-light fa-{{ $icon }} text-primary"></i>
                        {{ $label }}
                    </span>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[7px] font-black uppercase tracking-widest text-slate-400/80">{{ __('member.attendance.my_status') }}:</span>
                        <span class="js-event-status-badge px-2 py-0.5 sm:px-2.5 rounded-lg text-[8px] sm:text-[9px] font-black uppercase tracking-widest shadow-sm {{ $statusColors[$status] }} flex items-center gap-1">
                            @if($status === 'confirmed')
                                <i class="fa-light fa-circle-check text-[10px]"></i>
                            @elseif($status === 'declined')
                                <i class="fa-light fa-circle-xmark text-[10px]"></i>
                            @elseif($status === 'maybe')
                                <i class="fa-light fa-circle-question text-[10px]"></i>
                            @else
                                <i class="fa-light fa-clock text-[10px]"></i>
                            @endif
                            <span class="js-event-status-label">{{ $statusLabels[$status] }}</span>
                        </span>
                    </div>
                </div>
                <h4 class="text-base sm:text-lg font-black text-secondary leading-tight truncate tracking-tight">
                    @if($type === 'match')
                        {{ $data->team?->name }} <span class="text-primary italic mx-1">vs</span> {{ $data->opponent?->name }}
                    @elseif($type === 'training')
                        {{ $label }}
                    @else
                        {{ $data->title }}
                    @endif
                </h4>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5 sm:mt-2 text-[10px] sm:text-[11px] text-slate-500 font-bold italic opacity-80">
                    <span class="flex items-center gap-1.5"><i class="fa-light fa-clock text-primary"></i> {{ $time->format('H:i') }}</span>
                    @if($data->location)
                        <span class="flex items-center gap-1.5"><i class="fa-light fa-map-pin text-primary"></i> {{ Str::limit($data->location, 20) }}</span>
                    @endif
                </div>
            </div>
        </a>
    </div>

    <div class="flex items-center flex-wrap lg:flex-nowrap justify-between lg:justify-end gap-x-3 gap-y-4 sm:gap-x-6 w-full lg:w-auto pt-4 lg:pt-0 border-t lg:border-t-0 border-slate-100 lg:pl-0 {{ $showActions && $canChange && !($type === 'match' && $data->has_score) ? 'pl-[calc(1.75rem+1rem)] sm:pl-[calc(1.75rem+1.25rem)]' : 'pl-0' }}">
        <div class="flex items-center gap-2 sm:gap-4">
            <!-- Attendance Stats -->
            <div class="flex items-center gap-3 sm:gap-4 bg-slate-50/50 px-3 sm:px-4 py-2 rounded-2xl border border-slate-100/50">
                <div class="flex flex-col items-center" title="{{ __('member.attendance.status.confirmed') }}">
                    <span class="text-[10px] sm:text-[11px] font-black text-emerald-600 leading-none mb-1 sm:mb-1.5">{{ $confirmedCount }}</span>
                    <i class="fa-solid fa-circle-check text-[9px] sm:text-[10px] text-emerald-200"></i>
                </div>
                <div class="flex flex-col items-center" title="{{ __('member.attendance.status.declined') }}">
                    <span class="text-[10px] sm:text-[11px] font-black text-rose-600 leading-none mb-1 sm:mb-1.5">{{ $declinedCount }}</span>
                    <i class="fa-solid fa-circle-xmark text-[9px] sm:text-[10px] text-rose-200"></i>
                </div>
                <div class="flex flex-col items-center" title="{{ __('member.attendance.status.pending') }}">
                    <span class="text-[10px] sm:text-[11px] font-black text-slate-400 leading-none mb-1 sm:mb-1.5">{{ $pendingCount }}</span>
                    <i class="fa-solid fa-circle-question text-[9px] sm:text-[10px] text-slate-200"></i>
                </div>
            </div>

            <!-- Match Result (Score) -->
            @if($type === 'match' && $data->has_score)
                <div class="flex items-center gap-3 px-3 py-2 rounded-2xl border transition-all duration-300 shrink-0 relative overflow-hidden {{ $data->result_bg_color }} text-white shadow-sm border-white/10">
                    <div class="flex flex-col items-center">
                        <span class="text-[15px] font-black leading-none tabular-nums tracking-wider">
                            {{ $data->our_score }} : {{ $data->opponent_score }}
                        </span>
                        <span class="text-[7px] font-black text-white/70 uppercase tracking-[0.2em] leading-tight mt-1">
                            {{ __('matches.result_' . strtolower($data->result_letter)) }}
                        </span>
                    </div>
                </div>
            @endif

            <!-- Prediction Widget -->
            @if($type === 'match' && $data->prediction && !$data->has_score)
                @php
                    $winChance = round($data->prediction->probability_win * 100);
                    // Dynamický výpočet barvy (0% = červená, 50% = oranžová, 100% = zelená)
                    if ($winChance <= 50) {
                        $hue = ($winChance / 50) * 35; // 0-35 (red to orange-ish)
                    } else {
                        $hue = 35 + (($winChance - 50) / 50) * (105); // 35-140 (orange to success green)
                    }
                    $colorHsl = "hsl({$hue}, 75%, 45%)";
                    $bgHsl = "hsla({$hue}, 75%, 45%, 0.05)";
                    $glowHsl = "hsla({$hue}, 75%, 45%, 0.2)";
                    $borderHsl = "hsla({$hue}, 75%, 45%, 0.15)";
                @endphp
                <div @click.stop="predictionOpen = true"
                     class="cursor-pointer group/pred flex items-center gap-3 px-3 py-2 rounded-2xl border transition-all duration-300 shrink-0 relative overflow-hidden"
                     style="background: linear-gradient(135deg, {{ $bgHsl }}, white); border-color: {{ $borderHsl }};"
                     onmouseover="this.style.borderColor='hsla({{ $hue }}, 75%, 45%, 0.4)'; this.style.boxShadow='0 10px 15px -3px hsla({{ $hue }}, 75%, 45%, 0.1)';"
                     onmouseout="this.style.borderColor='{{ $borderHsl }}'; this.style.boxShadow='none';">

                    <div class="absolute inset-0 bg-white/40 translate-y-full group-hover/pred:translate-y-0 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="absolute inset-0 rounded-full blur-md group-hover/pred:blur-lg transition-all animate-pulse" style="background-color: {{ $glowHsl }};"></div>
                        <div class="relative w-9 h-9 rounded-xl bg-white flex items-center justify-center shadow-sm border group-hover/pred:scale-110 transition-transform" style="color: {{ $colorHsl }}; border-color: {{ $borderHsl }};">
                            <i class="fa-light fa-crystal-ball text-lg"></i>
                        </div>
                    </div>
                    <div class="flex flex-col relative">
                        <span class="text-[15px] font-black leading-none tabular-nums" style="color: {{ $colorHsl }};">
                            {{ $winChance }}%
                        </span>
                        <span class="text-[7px] font-black text-slate-400 uppercase tracking-[0.2em] leading-tight mt-1">
                            {{ __('matches.prediction.win_chance_short') ?? 'VÝHRA' }}
                        </span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        @if($showActions && $canChange)
            <div class="flex items-center gap-2 sm:gap-2.5 ml-auto lg:ml-0">
                <form action="{{ route('member.attendance.store', ['type' => $type, 'id' => $data->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="confirmed">
                    <button type="submit" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all shadow-sm hover:shadow-lg hover:shadow-emerald-500/20 active:scale-95 group/btn" title="{{ __('member.attendance.status.confirmed') }}">
                        <i class="fa-light fa-check text-xl"></i>
                    </button>
                </form>
                <form action="{{ route('member.attendance.store', ['type' => $type, 'id' => $data->id]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="declined">
                    <button type="submit" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white transition-all shadow-sm hover:shadow-lg hover:shadow-rose-500/20 active:scale-95 group/btn" title="{{ __('member.attendance.status.declined') }}">
                        <i class="fa-light fa-xmark text-xl"></i>
                    </button>
                </form>
                <a href="{{ route('member.attendance.show', ['type' => $type, 'id' => $data->id]) }}" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-slate-100 text-slate-400 hover:bg-primary hover:text-white transition-all shadow-sm hover:shadow-lg hover:shadow-primary/20 active:scale-95 group/btn" title="Detail">
                    <i class="fa-light fa-chevron-right text-lg"></i>
                </a>
            </div>
        @endif
    </div>
</div>
@if($type === 'match' && $data->prediction)
        <x-member.match-prediction-modal :match="$data" />
    @endif
</div>
