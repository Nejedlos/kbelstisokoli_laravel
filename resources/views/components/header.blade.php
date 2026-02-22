@props(['branding', 'navigation'])

<header x-data="{ mobileMenuOpen: false, searchOpen: false }" class="bg-white shadow-sm sticky top-0 z-50">
    <div class="container py-4 flex items-center justify-between gap-4">
        <!-- Logo -->
        <a href="{{ route('public.home') }}" class="flex items-center gap-3 shrink-0">
            @if($branding['logo_path'])
                <img src="{{ asset('storage/' . $branding['logo_path']) }}" alt="{{ brand_text($branding['club_name']) }}" class="h-12 w-auto">
                <div class="hidden md:block">
                    <span class="block font-display font-bold text-xl leading-none uppercase">{{ brand_text($branding['club_name']) }}</span>
                    <span class="block text-xs text-slate-500 font-medium tracking-wider uppercase">{{ brand_text($branding['slogan']) }}</span>
                </div>
            @endif
        </a>

        <!-- Desktop Navigation -->
        @if(!($branding['maintenance_mode'] ?? false))
        <nav class="hidden lg:flex items-center gap-8">
            @foreach($navigation as $item)
                <a href="{{ route($item['route']) }}"
                   class="font-bold uppercase text-sm tracking-wide text-slate-700 hover:text-primary transition {{ request()->routeIs($item['route']) ? 'text-primary border-b-2 border-primary' : '' }}">
                    {{ $item['title'] }}
                </a>
            @endforeach
        </nav>
        @endif

        <!-- Right Side / CTA -->
        <div class="flex items-center gap-2 sm:gap-4">
            <!-- Search Toggle -->
            <button @click="searchOpen = !searchOpen" class="p-2 text-slate-700 hover:text-primary focus:outline-none transition-colors" title="{{ __('search.title') }}">
                <i class="fa-light fa-magnifying-glass text-xl"></i>
            </button>

            <!-- Language Switcher -->
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-full text-[10px] font-black tracking-tighter shadow-sm border border-slate-200">
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'cs']) }}"
                   class="px-3 py-2 rounded-full transition-all cursor-pointer {{ app()->getLocale() === 'cs' ? 'bg-primary text-white shadow-sm' : 'text-slate-500 hover:text-primary hover:bg-white' }}">
                    CZ
                </a>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}"
                   class="px-3 py-2 rounded-full transition-all cursor-pointer {{ app()->getLocale() === 'en' ? 'bg-primary text-white shadow-sm' : 'text-slate-500 hover:text-primary hover:bg-white' }}">
                    EN
                </a>
            </div>

            @auth
                <div x-data="{ userMenuOpen: false }" class="relative">
                    <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 p-1 pr-3 rounded-full bg-slate-100 hover:bg-slate-200 transition-colors focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <span class="text-xs font-bold text-slate-700 hidden sm:block">{{ auth()->user()->name }}</span>
                        <i class="fa-light fa-chevron-down text-[10px] text-slate-400"></i>
                    </button>

                    <div x-show="userMenuOpen"
                         @click.away="userMenuOpen = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-slate-100 py-2 z-50">

                        <div class="px-4 py-2 border-b border-slate-50 mb-1">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-widest">{{ __('Uživatel') }}</span>
                            <span class="block text-sm font-bold text-slate-900 truncate">{{ auth()->user()->email }}</span>
                        </div>

                        <a href="{{ route('member.dashboard') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary transition-colors">
                            <i class="fa-light fa-user-gear w-5 text-center"></i>
                            {{ __('Členská sekce') }}
                        </a>

                        @can('access_admin')
                            <a href="/admin" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary transition-colors">
                                <i class="fa-light fa-lock-keyhole w-5 text-center"></i>
                                {{ __('Administrace') }}
                            </a>
                        @endcan

                        <div class="border-t border-slate-50 mt-1 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 w-full text-left transition-colors">
                                    <i class="fa-light fa-arrow-right-from-bracket w-5 text-center"></i>
                                    {{ __('Odhlásit se') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary hidden sm:inline-flex py-2 px-4 text-xs">
                    {{ __('Členská sekce') }}
                </a>
            @endauth

            <!-- Mobile Toggle -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 text-slate-700 hover:text-primary focus:outline-none transition-colors">
                <i x-show="!mobileMenuOpen" class="fa-light fa-bars-staggered text-2xl"></i>
                <i x-show="mobileMenuOpen" class="fa-light fa-xmark text-2xl"></i>
            </button>
        </div>
    </div>

    <!-- Search Overlay -->
    <div x-show="searchOpen"
         @click.away="searchOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="absolute inset-x-0 top-full bg-white border-t border-slate-100 shadow-xl py-6 z-40">
        <div class="container">
            <form action="{{ route('public.search') }}" method="GET" class="relative max-w-3xl mx-auto">
                <input type="text"
                       name="q"
                       placeholder="{{ __('search.placeholder') }}"
                       class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-6 py-3 text-lg focus:border-primary focus:ring-0 transition-all outline-none pr-12"
                       x-init="$watch('searchOpen', value => value && $el.focus())">
                <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors">
                    <i class="fa-light fa-magnifying-glass text-xl"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Mobile Menu Shell -->
    @if(!($branding['maintenance_mode'] ?? false))
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
                {{ __('Přihlášení pro členy') }}
            </a>
        </div>
    </div>
    @endif
</header>
