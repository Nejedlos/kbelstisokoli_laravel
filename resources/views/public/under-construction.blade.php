@extends('layouts.public')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center relative overflow-hidden bg-secondary">
    <!-- Dekorativní prvky -->
    <div class="absolute top-0 left-0 w-full h-full opacity-5 pointer-events-none">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <defs>
                <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                    <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5"/>
                </pattern>
            </defs>
            <rect width="100" height="100" fill="url(#grid)" />
        </svg>
    </div>

    <!-- Dynamické kruhy v pozadí -->
    <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-primary opacity-20 blur-3xl rounded-full animate-pulse"></div>
    <div class="absolute -left-20 -top-20 w-72 h-72 bg-accent opacity-20 blur-3xl rounded-full animate-pulse [animation-delay:1s]"></div>

    <div class="container relative z-10 text-center py-20">
        <div x-data="{ hover: false }"
             @mouseenter="hover = true"
             @mouseleave="hover = false"
             class="inline-flex items-center justify-center w-32 h-32 mb-12 relative transition-transform duration-500 transform"
             :class="hover ? 'scale-110 rotate-12' : ''">

            <!-- Aura kolem míče -->
            <div class="absolute inset-0 bg-primary/20 blur-xl rounded-full animate-ping"></div>

            <!-- Basketbalový míč (Ikona) -->
            <div class="relative w-full h-full bg-white/5 rounded-full border border-white/10 backdrop-blur-md flex items-center justify-center shadow-2xl overflow-hidden group">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-primary transition-colors duration-300" :class="hover ? 'text-white' : 'text-primary'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 2a14.5 14.5 0 0 0 0 20"></path>
                    <path d="M2 12h20"></path>
                    <path d="M12 2a14.5 14.5 0 0 1 0 20"></path>
                    <path d="M4.93 4.93a10 10 0 0 1 14.14 0"></path>
                    <path d="M4.93 19.07a10 10 0 0 0 14.14 0"></path>
                </svg>

                <!-- Efekt přejetí lesku -->
                <div class="absolute inset-0 w-full h-full bg-gradient-to-tr from-transparent via-white/10 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
            </div>
        </div>

        <h1 class="text-6xl md:text-8xl font-black text-white uppercase tracking-tighter mb-8 leading-none">
            {{ $title ?? 'Rozcvičujeme se' }}<span class="text-primary">.</span>
        </h1>

        <p class="max-w-3xl mx-auto text-xl md:text-3xl text-slate-300 font-medium leading-relaxed mb-16 opacity-90">
            {{ $text ?? 'Pracujeme na novém webu pro Kbelští sokoli. Brzy se vidíme na palubovce!' }}
        </p>

        <div class="flex flex-col md:flex-row items-center justify-center gap-6">
            @if($branding['socials']['facebook'] ?? null)
                <a href="{{ $branding['socials']['facebook'] }}" class="group relative px-8 py-4 bg-white/5 text-white font-bold uppercase tracking-widest text-sm rounded-club border border-white/10 backdrop-blur-md transition-all hover:bg-white/10 hover:border-white/20">
                    <span class="relative z-10">Facebook</span>
                    <div class="absolute bottom-0 left-0 w-full h-0.5 bg-primary transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </a>
            @endif
            @if($branding['socials']['instagram'] ?? null)
                <a href="{{ $branding['socials']['instagram'] }}" class="group relative px-8 py-4 bg-white/5 text-white font-bold uppercase tracking-widest text-sm rounded-club border border-white/10 backdrop-blur-md transition-all hover:bg-white/10 hover:border-white/20">
                    <span class="relative z-10">Instagram</span>
                    <div class="absolute bottom-0 left-0 w-full h-0.5 bg-primary transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </a>
            @endif
            <a href="/admin" class="btn btn-primary px-10 py-5 text-base hover:scale-105 transition-transform active:scale-95 shadow-primary/20 shadow-2xl">
                Administrace
            </a>
        </div>

        <div class="mt-32">
            <div class="flex items-center justify-center space-x-4 text-xs font-black uppercase tracking-[0.5em] text-slate-500">
                <span class="w-16 h-px bg-slate-800"></span>
                <span>Kbely Basketball Elite</span>
                <span class="w-16 h-px bg-slate-800"></span>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes shimmer {
    100% { transform: translateX(100%); }
}
</style>
@endsection
