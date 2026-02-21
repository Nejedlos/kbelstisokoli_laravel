@props(['branding', 'navigation'])

<header x-data="{ mobileMenuOpen: false }" class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container py-4 flex items-center justify-between">
        <!-- Logo -->
        <a href="{{ route('public.home') }}" class="flex items-center gap-3">
            @if($branding['logo_path'])
                <img src="{{ asset('storage/' . $branding['logo_path']) }}" alt="{{ $branding['club_name'] }}" class="h-12 w-auto">
            @else
                <div class="bg-primary text-white p-2 rounded-club font-display font-bold text-xl uppercase tracking-tighter">
                    {{ $branding['club_short_name'] }}
                </div>
            @endif
            <div class="hidden md:block">
                <span class="block font-display font-bold text-xl leading-none uppercase">{{ $branding['club_name'] }}</span>
                <span class="block text-xs text-slate-500 font-medium tracking-wider uppercase">{{ $branding['slogan'] }}</span>
            </div>
        </a>

        <!-- Desktop Navigation -->
        <nav class="hidden lg:flex items-center gap-8">
            @foreach($navigation as $item)
                <a href="{{ route($item['route']) }}"
                   class="font-bold uppercase text-sm tracking-wide text-slate-700 hover:text-primary transition {{ request()->routeIs($item['route']) ? 'text-primary border-b-2 border-primary' : '' }}">
                    {{ $item['title'] }}
                </a>
            @endforeach
        </nav>

        <!-- Right Side / CTA -->
        <div class="flex items-center gap-4">
            <a href="{{ route('login') }}" class="btn btn-primary hidden sm:inline-flex py-2 px-4 text-xs">
                Členská sekce
            </a>

            <!-- Mobile Toggle -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 text-slate-700 hover:text-primary focus:outline-none">
                <svg x-show="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg x-show="mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu Shell -->
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="lg:hidden bg-white border-t border-slate-100 py-4 absolute w-full shadow-xl">
        <div class="container flex flex-col gap-4">
            @foreach($navigation as $item)
                <a href="{{ route($item['route']) }}"
                   class="font-bold uppercase text-base tracking-wide py-2 border-b border-slate-50 {{ request()->routeIs($item['route']) ? 'text-primary' : 'text-slate-700' }}">
                    {{ $item['title'] }}
                </a>
            @endforeach
            <a href="{{ route('login') }}" class="btn btn-primary mt-4">
                Přihlášení pro členy
            </a>
        </div>
    </div>
</header>
