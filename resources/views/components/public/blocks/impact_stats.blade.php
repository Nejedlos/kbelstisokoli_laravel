@php
    $stats = $data['stats'] ?? [
        ['number' => '250+', 'label' => __('general.stats.active_players'), 'icon' => 'fa-basketball'],
        ['number' => '10', 'label' => __('general.stats.teams'), 'icon' => 'fa-users'],
        ['number' => '15', 'label' => __('general.stats.coaches'), 'icon' => 'fa-user-tie'],
        ['number' => '1921', 'label' => __('general.stats.founded'), 'icon' => 'fa-calendar-star'],
    ];
    $title = $data['title'] ?? __('general.stats.title');
    $subtitle = $data['subtitle'] ?? __('general.stats.subtitle');
@endphp

<section class="section-padding bg-slate-900 text-white relative overflow-hidden">
    {{-- Decorative Background --}}
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none opacity-10">
        <i class="fa-light fa-basketball text-[40rem] absolute -top-40 -right-40 rotate-12"></i>
    </div>

    <div class="container relative z-10">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-5xl font-black uppercase tracking-tighter mb-4">
                {!! nl2br(e($title)) !!}
            </h2>
            @if($subtitle)
                <p class="text-slate-400 max-w-2xl mx-auto font-medium">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 md:gap-12">
            @foreach($stats as $stat)
                <div class="text-center group" x-data="{ count: 0, target: {{ (int) filter_var($stat['number'], FILTER_SANITIZE_NUMBER_INT) }} }"
                     x-intersect.once="let start = 0; let duration = 2000; let step = (timestamp) => {
                        if (!start) start = timestamp;
                        let progress = timestamp - start;
                        count = Math.min(Math.floor((progress / duration) * target), target);
                        if (progress < duration) window.requestAnimationFrame(step);
                     }; window.requestAnimationFrame(step)">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/10 mb-6 group-hover:bg-primary group-hover:scale-110 transition-all duration-500">
                        <i class="fa-light {{ $stat['icon'] }} text-2xl text-primary group-hover:text-white transition-colors"></i>
                    </div>
                    <div class="text-4xl md:text-6xl font-black text-white mb-2 tracking-tighter">
                        <span x-text="count">0</span>{{ str_contains($stat['number'], '+') ? '+' : '' }}
                    </div>
                    <div class="text-xs md:text-sm font-bold uppercase tracking-widest text-slate-400 group-hover:text-slate-200 transition-colors">
                        {{ $stat['label'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
