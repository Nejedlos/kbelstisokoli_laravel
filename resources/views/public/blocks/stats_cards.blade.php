@php
    $stats = $data['stats'] ?? [];
@endphp

<section @class([
    'block-stats-cards py-16',
    'bg-secondary text-white' => ($data['variant'] ?? 'dark') === 'dark',
    'bg-white text-secondary border-y border-slate-100' => ($data['variant'] ?? 'dark') === 'light',
])>
    <div class="container mx-auto px-4">
        <div @class([
            'grid gap-8',
            'grid-cols-2 lg:grid-cols-' . count($stats) => count($stats) > 0,
            'grid-cols-1' => count($stats) === 0,
        ])>
            @foreach($stats as $stat)
                <div class="text-center group p-6 rounded-club hover:bg-white/5 transition-colors">
                    @if($stat['icon'] ?? null)
                        <div class="mb-4 flex justify-center text-primary transform group-hover:scale-110 transition-transform duration-300">
                             <i class="fa-light fa-{{ $stat['icon'] }} text-4xl"></i>
                        </div>
                    @endif
                    <div class="text-5xl md:text-6xl font-black text-primary mb-2 tracking-tighter tabular-nums drop-shadow-sm">
                        {{ $stat['value'] ?? '' }}
                    </div>
                    <div @class([
                        'text-xs md:text-sm font-black uppercase tracking-[0.2em] leading-tight',
                        'text-slate-400' => ($data['variant'] ?? 'dark') === 'dark',
                        'text-slate-500' => ($data['variant'] ?? 'dark') === 'light',
                    ])>
                        {{ $stat['label'] ?? '' }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
