@php
    $style = $data['style'] ?? 'primary';
@endphp

<section @class([
    'block-cta py-20 relative overflow-hidden',
    'bg-primary text-white shadow-[inset_0_0_100px_rgba(0,0,0,0.2)]' => $style === 'primary',
    'bg-secondary text-white shadow-[inset_0_0_100px_rgba(255,255,255,0.05)]' => $style === 'secondary',
    'bg-slate-50 text-secondary border-y border-slate-200' => $style === 'light',
    'bg-white border-y border-slate-100' => $style === 'outline',
])>
    {{-- Decorative elements --}}
    <div @class([
        'absolute top-0 left-0 w-full h-full pointer-events-none hero-mesh',
        'opacity-10' => $style !== 'light',
        'opacity-5' => $style === 'light',
    ])>
        <div @class([
            'absolute top-0 right-0 w-64 h-64 border-t-8 border-r-8 -mr-12 -mt-12',
            'border-white' => $style !== 'light',
            'border-primary/10' => $style === 'light',
        ])></div>
        <div @class([
            'absolute bottom-0 left-0 w-64 h-64 border-b-8 border-l-8 -ml-12 -mb-12',
            'border-white' => $style !== 'light',
            'border-primary/10' => $style === 'light',
        ])></div>
    </div>

    @if($style === 'light')
        <div class="absolute inset-0 z-0 pointer-events-none opacity-40">
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-primary/5 via-transparent to-accent/5"></div>
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary/10 blur-[120px] rounded-full"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-accent/10 blur-[120px] rounded-full"></div>
        </div>
    @endif

    <div class="container mx-auto px-4 relative z-10">
        <div @class([
            'max-w-4xl mx-auto',
            'text-center' => ($data['alignment'] ?? 'center') === 'center',
            'text-left' => ($data['alignment'] ?? 'center') === 'left',
            'text-right' => ($data['alignment'] ?? 'center') === 'right',
        ])>
            @if($data['title'] ?? null)
                <h2 @class([
                    'text-3xl md:text-5xl lg:text-6xl font-black uppercase tracking-tighter mb-8 leading-tight',
                    'text-white' => in_array($style, ['primary', 'secondary']),
                    'text-secondary' => in_array($style, ['outline', 'light']),
                ])>
                    {!! nl2br(e($data['title'])) !!}
                </h2>
            @endif

            @if($data['text'] ?? null)
                <p @class([
                    'text-lg md:text-xl mb-12 font-medium opacity-90 leading-relaxed',
                    'text-white/80' => in_array($style, ['primary', 'secondary']),
                    'text-slate-500' => in_array($style, ['outline', 'light']),
                ])>
                    {{ $data['text'] }}
                </p>
            @endif

            <div @class([
                'flex gap-4 flex-wrap',
                'justify-center' => ($data['alignment'] ?? 'center') === 'center',
                'justify-start' => ($data['alignment'] ?? 'center') === 'left',
                'justify-end' => ($data['alignment'] ?? 'center') === 'right',
            ])>
                @if($data['button_text'] ?? null)
                    <a href="{{ $data['button_url'] ?? '#' }}" @class([
                        'btn px-12 py-5 text-xl font-black shadow-2xl transition-all duration-300 transform hover:scale-105 group',
                        'bg-white text-primary hover:bg-slate-50' => $style === 'primary',
                        'bg-primary text-white hover:bg-primary-hover' => in_array($style, ['secondary', 'light']),
                        'btn-primary' => $style === 'outline',
                    ])>
                        <span>{{ $data['button_text'] ?? 'Zjistit více' }}</span>
                        <i class="fa-light fa-chevron-right ml-3 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                @endif

                @if($data['secondary_button_text'] ?? null)
                    <a href="{{ $data['secondary_button_url'] ?? '#' }}" @class([
                        'btn px-12 py-5 text-xl font-black transition-all duration-300 transform hover:scale-105 group',
                        'btn-outline-white' => in_array($style, ['primary', 'secondary']),
                        'btn-outline-primary' => in_array($style, ['outline', 'light']),
                    ])>
                        <span>{{ $data['secondary_button_text'] }}</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Floating basketball icon --}}
    <div @class([
        'absolute -bottom-12 -right-12 pointer-events-none',
        'text-white/5' => $style !== 'light',
        'text-primary/5' => $style === 'light',
    ])>
        <i class="fa-light fa-basketball-hoop text-[15rem] -rotate-12"></i>
    </div>
</section>
