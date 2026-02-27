@php
    $branding = app(\App\Services\BrandingService::class)->getSettings();
    $primaryColor = $branding['colors']['red'] ?? '#e11d48';
    $primaryHover = '#be123c'; // Fallback
@endphp

<div x-data="{
        show: false,
        scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
     }"
     @scroll.window.throttle.50ms="show = window.scrollY > 500"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-10 scale-90"
     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
     x-transition:leave-end="opacity-0 translate-y-10 scale-90"
     class="fixed bottom-8 right-8 z-[9999]"
     style="display: none;">

    <button @click="scrollToTop()"
            class="group relative flex items-center justify-center w-14 h-14 rounded-full text-white shadow-2xl transition-all duration-300 overflow-hidden hover:scale-110 active:scale-95"
            style="background-color: {{ $primaryColor }}; shadow: 0 20px 25px -5px {{ $primaryColor }}4D;"
            title="{{ __('Zpět nahoru') }}">

        {{-- Shine effect --}}
        <div class="absolute inset-0 bg-gradient-to-tr from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>

        {{-- Basketball icon --}}
        <i class="fa-light fa-basketball text-2xl group-hover:rotate-[360deg] transition-transform duration-700 ease-in-out"></i>

        {{-- Tooltip/Label on hover (optional, discreet) --}}
        <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-secondary text-white text-[10px] font-black uppercase tracking-widest px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap border border-white/10">
            {{ __('Nahoru') }}
        </div>

        {{-- Pulse rings --}}
        <div class="absolute inset-0 rounded-full border border-white/30 animate-ping opacity-20 pointer-events-none"></div>
    </button>
</div>
