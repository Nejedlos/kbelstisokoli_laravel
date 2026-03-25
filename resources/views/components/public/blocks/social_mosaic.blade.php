@php
    $title = $data['title'] ?? __('general.social.title');
    $subtitle = $data['subtitle'] ?? __('general.social.subtitle');
    $cta_label = $data['cta_label'] ?? __('general.social.cta');
    $cta_url = $data['cta_url'] ?? 'https://www.instagram.com/kbelstisokoli/';
    $images = $data['images'] ?? [
        ['url' => 'assets/img/home/social-1.jpg', 'size' => 'small'],
        ['url' => 'assets/img/home/social-2.jpg', 'size' => 'large'],
        ['url' => 'assets/img/home/social-3.jpg', 'size' => 'small'],
        ['url' => 'assets/img/home/social-4.jpg', 'size' => 'small'],
        ['url' => 'assets/img/home/social-5.jpg', 'size' => 'small'],
        ['url' => 'assets/img/home/social-6.jpg', 'size' => 'large'],
        ['url' => 'assets/img/home/social-7.jpg', 'size' => 'small'],
        ['url' => 'assets/img/home/social-8.jpg', 'size' => 'small'],
    ];
@endphp

<section class="section-padding bg-white overflow-hidden">
    <div class="container mb-12">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="max-w-2xl">
                <span class="inline-block text-primary font-bold uppercase tracking-widest text-[11px] mb-4">
                    {{ __('general.social.eyebrow') }}
                </span>
                <h2 class="text-3xl md:text-5xl font-black uppercase tracking-tighter mb-4 leading-none">
                    {!! nl2br(e($title)) !!}
                </h2>
                <p class="text-slate-500 font-medium">
                    {{ $subtitle }}
                </p>
            </div>
            <a href="{{ $cta_url }}" target="_blank" rel="noopener noreferrer"
               class="group inline-flex items-center gap-3 font-black uppercase text-xs tracking-widest text-slate-900 border-b-2 border-primary pb-1 hover:text-primary transition-colors">
                <i class="fa-brands fa-instagram text-lg"></i>
                {{ $cta_label }}
            </a>
        </div>
    </div>

    {{-- The Mosaic Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 px-4 max-w-[1920px] mx-auto">
        @foreach($images as $image)
            <div class="relative group overflow-hidden rounded-2xl aspect-square {{ $image['size'] === 'large' ? 'md:col-span-2 md:row-span-2' : '' }}">
                <img src="{{ asset($image['url']) }}" alt="Instagram" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                <div class="absolute inset-0 bg-primary/40 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
                    <i class="fa-brands fa-instagram text-white text-3xl opacity-0 group-hover:opacity-100 group-hover:scale-110 transition-all duration-500 delay-100"></i>
                </div>
            </div>
        @endforeach
    </div>
</section>
