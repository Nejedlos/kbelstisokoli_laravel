@php
    $style = $data['style'] ?? 'primary';
@endphp

<section @class([
    'block-cta py-20 relative overflow-hidden',
    'bg-primary text-white shadow-[inset_0_0_100px_rgba(0,0,0,0.2)]' => $style === 'primary',
    'bg-secondary text-white shadow-[inset_0_0_100px_rgba(255,255,255,0.05)]' => $style === 'secondary',
    'bg-white border-y border-slate-100' => $style === 'outline',
])>
    {{-- Decorative elements --}}
    <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none hero-mesh">
        <div class="absolute top-0 right-0 w-64 h-64 border-t-8 border-r-8 border-white -mr-12 -mt-12"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 border-b-8 border-l-8 border-white -ml-12 -mb-12"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
            @if($data['title'] ?? null)
                <h2 @class([
                    'text-3xl md:text-5xl lg:text-6xl font-black uppercase tracking-tighter mb-8 leading-tight',
                    'text-white' => in_array($style, ['primary', 'secondary']),
                    'text-secondary' => $style === 'outline',
                ])>
                    {!! nl2br(e($data['title'])) !!}
                </h2>
            @endif

            @if($data['text'] ?? null)
                <p @class([
                    'text-lg md:text-xl mb-12 font-medium opacity-90 leading-relaxed',
                    'text-white/80' => in_array($style, ['primary', 'secondary']),
                    'text-slate-500' => $style === 'outline',
                ])>
                    {{ $data['text'] }}
                </p>
            @endif

            <div class="flex justify-center">
                <a href="{{ $data['button_url'] ?? '#' }}" @class([
                    'btn px-12 py-5 text-xl font-black shadow-2xl transition-all duration-300 transform hover:scale-105 group',
                    'bg-white text-primary hover:bg-slate-50' => $style === 'primary',
                    'bg-primary text-white hover:bg-primary-hover' => $style === 'secondary',
                    'btn-primary' => $style === 'outline',
                ])>
                    <span>{{ $data['button_text'] ?? 'Zjistit více' }}</span>
                    <i class="fa-light fa-chevron-right ml-3 group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Floating basketball icon --}}
    <div class="absolute -bottom-12 -right-12 text-white/5 pointer-events-none">
        <i class="fa-light fa-basketball-hoop text-[15rem] -rotate-12"></i>
    </div>
</section>
