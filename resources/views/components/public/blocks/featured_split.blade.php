@php
    $image_url = $data['image_url'] ?? 'assets/img/home/recruitment-split.jpg';
    $title = $data['title'] ?? __('general.recruitment.title');
    $subtitle = $data['subtitle'] ?? __('general.recruitment.subtitle');
    $cta_label = $data['cta_label'] ?? __('general.recruitment.cta');
    $cta_url = $data['cta_url'] ?? route('public.news.index');
    $alignment = $data['alignment'] ?? 'left'; // Image on the left
@endphp

<section class="relative overflow-hidden bg-white">
    <div class="flex flex-col md:flex-row min-h-[500px]">
        {{-- Image Side --}}
        <div class="w-full md:w-1/2 relative min-h-[400px] md:min-h-0 {{ $alignment === 'right' ? 'md:order-last' : '' }}">
            <img src="{{ asset($image_url) }}" alt="{{ $title }}" class="absolute inset-0 w-full h-full object-cover">
            {{-- Overlay Decoration --}}
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 via-transparent to-transparent"></div>
            <div class="absolute bottom-12 left-12 right-12 text-white/20 font-black text-[100px] uppercase tracking-tighter leading-none pointer-events-none -rotate-3 select-none">
                {{ __('general.stats.founded_year') }}
            </div>
        </div>

        {{-- Content Side --}}
        <div class="w-full md:w-1/2 flex items-center bg-slate-50 section-padding">
            <div class="container-narrow px-8 md:px-16">
                <div class="mb-8">
                    <span class="inline-block text-primary font-bold uppercase tracking-widest text-[11px] mb-4 py-1 px-3 bg-primary/5 rounded-full border border-primary/10">
                        {{ __('general.recruitment.eyebrow') }}
                    </span>
                    <h2 class="text-3xl md:text-5xl font-black uppercase tracking-tighter mb-6 leading-[0.9]">
                        {!! nl2br(e($title)) !!}
                    </h2>
                    <p class="text-slate-600 text-lg md:text-xl font-medium mb-8">
                        {{ $subtitle }}
                    </p>
                </div>

                <a href="{{ $cta_url }}" class="group relative inline-flex items-center gap-4 bg-primary text-white font-black uppercase text-sm tracking-widest py-5 px-10 rounded-xl overflow-hidden shadow-xl shadow-primary/20 hover:shadow-2xl hover:shadow-primary/30 transition-all hover:-translate-y-1">
                    <span>{{ $cta_label }}</span>
                    <i class="fa-light fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </a>
            </div>
        </div>
    </div>
</section>
