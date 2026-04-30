@props(['branding', 'navigation'])

@cacheFragment('fragment_header_'.app()->getLocale().'_'.(auth()->check() ? auth()->id() : 'guest').'_'.md5(request()->fullUrl()), 3600)
<header x-data="{ mobileMenuOpen: false, searchOpen: false }" class="bg-white shadow-sm">
    <div class="container py-2.5 lg:py-4 flex items-center justify-between gap-3 lg:gap-4">
        <!-- Logo -->
        <a href="{{ url('/') }}" @wireNavigate class="flex items-center gap-2.5 lg:gap-3 shrink-0">
            @php
                $teamLogo = $branding['team_logo'] ?? null;
                $isTeamLogoEnabled = $teamLogo['enabled_header'] ?? true;
            @endphp

            @if($isTeamLogoEnabled)
                <picture class="transition-transform duration-500 hover:rotate-3 hover:scale-110">
                    <source srcset="{{ web_asset($teamLogo['paths']['mini'] ?? '', true) }}" type="image/webp">
                    <img src="{{ web_asset($teamLogo['paths']['mini'] ?? '', false) }}"
                         alt="Kbelští sokoli C & E logo"
                         class="object-contain h-[26px] lg:h-[36px] xl:h-[40px] w-auto"
                         loading="eager"
                         fetchpriority="high"
                         id="header-team-logo"
                    >
                </picture>
@elseif($branding['logo_path'])
                <img src="{{ web_asset($branding['logo_path']) }}" alt="{{ brand_text($branding['club_name']) }}" class="h-8 lg:h-12 w-auto">
            @endif

            <div class="hidden md:block opacity-80">
                <span class="block font-display font-bold text-sm lg:text-base leading-tight uppercase tracking-tight">{{ brand_text($branding['club_name']) }}</span>
                <span class="block text-[9px] lg:text-[10px] text-slate-400 font-medium tracking-widest uppercase leading-snug">{{ brand_text($branding['slogan']) }}</span>
            </div>
        </a>

        <!-- Desktop Navigation -->
        @if(!($branding['maintenance_mode'] ?? false))
        <nav class="hidden xl:flex items-center gap-x-3 2xl:gap-x-10">
            {{-- Úvod --}}
            <a href="{{ url('/') }}" @wireNavigate
               class="font-bold uppercase text-[11px] xl:text-sm tracking-wide text-slate-700 hover:text-primary transition py-2 {{ request()->is('/') ? 'text-primary border-b-2 border-primary' : '' }}">
                {{ __('general.nav.home') }}
            </a>

            {{-- Týmy --}}
            <a href="{{ route('public.teams.index') }}" @wireNavigate
               class="font-bold uppercase text-[11px] xl:text-sm tracking-wide text-slate-700 hover:text-primary transition py-2 {{ request()->routeIs('public.teams.*') ? 'text-primary border-b-2 border-primary' : '' }}">
                {{ __('general.nav.teams') }}
            </a>

            {{-- Program Dropdown --}}
            <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" class="relative group py-2">
                <button class="flex items-center gap-1.5 font-bold uppercase text-[11px] xl:text-sm tracking-wide text-slate-700 group-hover:text-primary transition focus:outline-none"
                        :aria-expanded="open"
                        aria-haspopup="true">
                    {{ __('general.nav.program') }}
                    <i class="fa-light fa-chevron-down text-[10px] transition-transform duration-300 group-hover:rotate-180"></i>
                </button>
                <div x-show="open"
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="absolute left-0 mt-1 w-56 bg-white rounded-2xl shadow-2xl border border-slate-100 py-3 z-50 overflow-hidden"
                     @click="open = false">
                    <a href="{{ route('public.matches.index') }}" @wireNavigate
                       class="flex items-center px-5 py-2.5 text-[10px] xl:text-[11px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 hover:text-primary transition-all duration-200 {{ request()->routeIs('public.matches.*') ? 'text-primary bg-primary/5' : '' }}">
                        {{ __('general.nav.matches') }}
                    </a>
                    <a href="{{ route('public.trainings.index') }}" @wireNavigate
                       class="flex items-center px-5 py-2.5 text-[10px] xl:text-[11px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 hover:text-primary transition-all duration-200 {{ request()->routeIs('public.trainings.*') ? 'text-primary bg-primary/5' : '' }}">
                        {{ __('general.nav.trainings') }}
                    </a>
                    <a href="{{ route('public.events.index') }}" @wireNavigate
                       class="flex items-center px-5 py-2.5 text-[10px] xl:text-[11px] font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 hover:text-primary transition-all duration-200 {{ request()->routeIs('public.events.*') ? 'text-primary bg-primary/5' : '' }}">
                        {{ __('general.nav.events') }}
                    </a>
                </div>
            </div>

            {{-- Nábor --}}
            <a href="{{ route('public.recruitment.index') }}" @wireNavigate
               class="font-bold uppercase text-[11px] xl:text-sm tracking-wide text-slate-700 hover:text-primary transition py-2 {{ request()->routeIs('public.recruitment.*') ? 'text-primary border-b-2 border-primary' : '' }}">
                {{ __('general.nav.recruitment') }}
            </a>

            {{-- Novinky --}}
            <a href="{{ route('public.news.index') }}" @wireNavigate
               class="font-bold uppercase text-[11px] xl:text-sm tracking-wide text-slate-700 hover:text-primary transition py-2 {{ request()->routeIs('public.news.*') ? 'text-primary border-b-2 border-primary' : '' }}">
                {{ __('general.nav.news') }}
            </a>

            {{-- Galerie --}}
            <a href="{{ route('public.galleries.index') }}" @wireNavigate
               class="font-bold uppercase text-[11px] xl:text-sm tracking-wide text-slate-700 hover:text-primary transition py-2 {{ request()->routeIs('public.galleries.*') ? 'text-primary border-b-2 border-primary' : '' }}">
                {{ __('general.nav.gallery') }}
            </a>

            {{-- Historie --}}
            <a href="{{ route('public.history.index') }}" @wireNavigate
               class="font-bold uppercase text-[11px] xl:text-sm tracking-wide text-slate-700 hover:text-primary transition py-2 {{ request()->routeIs('public.history.*') ? 'text-primary border-b-2 border-primary' : '' }}">
                {{ __('general.nav.history') }}
            </a>

            {{-- Kontakt --}}
            <a href="{{ route('public.contact.index') }}" @wireNavigate
               class="font-bold uppercase text-[11px] xl:text-sm tracking-wide text-slate-700 hover:text-primary transition py-2 {{ request()->routeIs('public.contact.*') ? 'text-primary border-b-2 border-primary' : '' }}">
                {{ __('general.nav.contact') }}
            </a>
        </nav>
        @endif

        <!-- Right Side / CTA -->
        <div class="flex items-center gap-2 sm:gap-4">
            <!-- Search Toggle -->
            <button @click="searchOpen = !searchOpen" class="p-2 text-slate-700 hover:text-primary focus:outline-none transition-colors" title="{{ __('search.title') }}">
                <i class="fa-light fa-magnifying-glass text-xl"></i>
            </button>

            <!-- Language Switcher -->
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-full text-[10px] font-black tracking-widest shadow-sm border border-slate-200">
                <a href="{{ Route::has('language.switch') ? route('language.switch', ['lang' => 'cs']) : url('/language/cs') }}"
                   class="px-3 py-1.5 rounded-full transition-all cursor-pointer {{ app()->getLocale() === 'cs' ? 'bg-primary text-white shadow-sm' : 'text-slate-500 hover:text-primary hover:bg-white' }}"
                   data-track-click="language_switch"
                   data-track-label="CS"
                   data-track-category="ux">
                    CZ
                </a>
                <a href="{{ Route::has('language.switch') ? route('language.switch', ['lang' => 'en']) : url('/language/en') }}"
                   class="px-3 py-1.5 rounded-full transition-all cursor-pointer {{ app()->getLocale() === 'en' ? 'bg-primary text-white shadow-sm' : 'text-slate-500 hover:text-primary hover:bg-white' }}"
                   data-track-click="language_switch"
                   data-track-label="EN"
                   data-track-category="ux">
                    EN
                </a>
            </div>

            @auth
                @php
                    $logoutRoute = Route::has('logout') ? route('logout') : url('/logout');
                @endphp
                <div x-data="{ userMenuOpen: false }" class="relative">
                    <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 p-1 pr-3 rounded-full bg-slate-100 hover:bg-slate-200 transition-colors focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-bold text-xs">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <span class="text-xs font-bold text-slate-700 hidden sm:block">{{ auth()->user()->name }}</span>
                        <i class="fa-light fa-chevron-down text-[10px] text-slate-400"></i>
                    </button>

                    <div x-show="userMenuOpen"
                         x-cloak
                         @click.away="userMenuOpen = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-slate-100 py-2 z-50">

                        <div class="px-4 py-2 border-b border-slate-50 mb-1">
                            <span class="block text-xs font-bold text-slate-400 uppercase tracking-widest">{{ __('nav.user') }}</span>
                            <span class="block text-sm font-bold text-slate-900 truncate">{{ auth()->user()->email }}</span>
                        </div>

                        <a href="{{ url('/clenska-sekce/dashboard') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary transition-colors">
                            <i class="fa-light fa-user-gear w-5 text-center"></i>
                            {{ __('nav.member_section') }}
                        </a>

                        @if(auth()->user()?->canAccessAdmin())
                            <a href="{{ url(config('filament.panels.admin.path', 'admin')) }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-primary transition-colors">
                                <i class="fa-light fa-lock-keyhole w-5 text-center"></i>
                                {{ __('nav.administration') }}
                            </a>
                        @endif

                        <div class="border-t border-slate-50 mt-1 pt-1">
                            <form method="POST" action="{{ $logoutRoute }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 w-full text-left transition-colors">
                                    <i class="fa-light fa-arrow-right-from-bracket w-5 text-center"></i>
                                    {{ __('nav.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ Route::has('login') ? route('login') : url('/login') }}" class="btn btn-primary hidden sm:inline-flex py-2 px-4 text-xs">
                    {{ __('nav.member_section') }}
                </a>
            @endauth

            <button @click="mobileMenuOpen = !mobileMenuOpen" class="xl:hidden p-2 -mr-1 text-slate-700 hover:text-primary focus:outline-none transition-colors"
                    aria-label="{{ __('general.nav.toggle_menu') }}"
                    :aria-expanded="mobileMenuOpen">
                <i x-show="!mobileMenuOpen" class="fa-light fa-bars-staggered text-2xl"></i>
                <i x-show="mobileMenuOpen" class="fa-light fa-xmark text-2xl"></i>
            </button>
        </div>
    </div>

    <!-- Search Overlay -->
    <div x-show="searchOpen"
         x-cloak
         @keydown.escape.window="searchOpen = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="absolute inset-x-0 top-full bg-white border-t border-slate-100 shadow-2xl py-8 md:py-12 z-40">
        <div class="container relative">
            <form action="{{ Route::has('public.search') ? route('public.search') : url('/hledat') }}" method="GET" class="relative max-w-3xl mx-auto">
                <input type="text"
                       name="q"
                       placeholder="{{ __('search.placeholder') }}"
                       class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl px-6 py-5 md:py-4 text-lg md:text-xl focus:border-primary focus:ring-0 transition-all outline-none pr-16 shadow-inner"
                       x-init="$watch('searchOpen', value => value && setTimeout(() => $el.focus(), 100))">
                <button type="submit" class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary transition-colors p-2" aria-label="{{ __('Search') }}">
                    <i class="fa-light fa-magnifying-glass text-2xl"></i>
                </button>
            </form>

            <button @click="searchOpen = false" class="absolute -top-4 right-0 p-2 text-slate-400 hover:text-primary transition-colors md:hidden">
                <i class="fa-light fa-xmark text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Menu Shell -->
    @if(!($branding['maintenance_mode'] ?? false))
    <div x-show="mobileMenuOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-10"
         class="xl:hidden bg-white border-t border-slate-100 py-6 absolute w-full shadow-2xl z-40 max-h-[85vh] overflow-y-auto">
         <div class="container pb-8">
            <div class="flex flex-col gap-1">
                {{-- Úvod --}}
                <a href="{{ url('/') }}" @wireNavigate
                   class="block font-black uppercase text-xs tracking-widest py-4 px-4 border-b border-slate-50 hover:bg-slate-50 transition-colors {{ request()->is('/') ? 'text-primary bg-primary/5' : 'text-slate-700' }}">
                    {{ __('general.nav.home') }}
                </a>

                {{-- Týmy --}}
                <a href="{{ route('public.teams.index') }}" @wireNavigate
                   class="block font-black uppercase text-xs tracking-widest py-4 px-4 border-b border-slate-50 hover:bg-slate-50 transition-colors {{ request()->routeIs('public.teams.*') ? 'text-primary bg-primary/5' : 'text-slate-700' }}">
                    {{ __('general.nav.teams') }}
                </a>

                {{-- Program Dropdown Mobile --}}
                <div x-data="{ open: {{ request()->routeIs('public.matches.*', 'public.trainings.*', 'public.events.*') ? 'true' : 'false' }} }" class="border-b border-slate-50">
                    <button @click="open = !open" class="flex items-center justify-between w-full font-black uppercase text-xs tracking-widest py-4 px-4 hover:bg-slate-50 text-slate-700 transition-colors focus:outline-none">
                        <span>{{ __('general.nav.program') }}</span>
                        <i class="fa-light fa-chevron-down text-[10px] transition-transform duration-300" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse x-cloak class="bg-slate-50/50 rounded-2xl mx-2 mb-2 overflow-hidden">
                        <div class="grid grid-cols-1 gap-0.5 p-1">
                            <a href="{{ route('public.matches.index') }}" @wireNavigate
                               class="flex items-center font-bold uppercase text-[10px] tracking-widest py-3 px-6 rounded-xl hover:bg-white hover:text-primary transition-all {{ request()->routeIs('public.matches.*') ? 'text-primary bg-white' : 'text-slate-500' }}">
                               {{ __('general.nav.matches') }}
                            </a>
                            <a href="{{ route('public.trainings.index') }}" @wireNavigate
                               class="flex items-center font-bold uppercase text-[10px] tracking-widest py-3 px-6 rounded-xl hover:bg-white hover:text-primary transition-all {{ request()->routeIs('public.trainings.*') ? 'text-primary bg-white' : 'text-slate-500' }}">
                               {{ __('general.nav.trainings') }}
                            </a>
                            <a href="{{ route('public.events.index') }}" @wireNavigate
                               class="flex items-center font-bold uppercase text-[10px] tracking-widest py-3 px-6 rounded-xl hover:bg-white hover:text-primary transition-all {{ request()->routeIs('public.events.*') ? 'text-primary bg-white' : 'text-slate-500' }}">
                               {{ __('general.nav.events') }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Nábor --}}
                <a href="{{ route('public.recruitment.index') }}" @wireNavigate
                   class="block font-black uppercase text-xs tracking-widest py-4 px-4 border-b border-slate-50 hover:bg-slate-50 transition-colors {{ request()->routeIs('public.recruitment.*') ? 'text-primary bg-primary/5' : 'text-slate-700' }}">
                    {{ __('general.nav.recruitment') }}
                </a>

                {{-- Novinky --}}
                <a href="{{ route('public.news.index') }}" @wireNavigate
                   class="block font-black uppercase text-xs tracking-widest py-4 px-4 border-b border-slate-50 hover:bg-slate-50 transition-colors {{ request()->routeIs('public.news.*') ? 'text-primary bg-primary/5' : 'text-slate-700' }}">
                    {{ __('general.nav.news') }}
                </a>

                {{-- Galerie --}}
                <a href="{{ route('public.galleries.index') }}" @wireNavigate
                   class="block font-black uppercase text-xs tracking-widest py-4 px-4 border-b border-slate-50 hover:bg-slate-50 transition-colors {{ request()->routeIs('public.galleries.*') ? 'text-primary bg-primary/5' : 'text-slate-700' }}">
                    {{ __('general.nav.gallery') }}
                </a>

                {{-- Historie --}}
                <a href="{{ route('public.history.index') }}" @wireNavigate
                   class="block font-black uppercase text-xs tracking-widest py-4 px-4 border-b border-slate-50 hover:bg-slate-50 transition-colors {{ request()->routeIs('public.history.*') ? 'text-primary bg-primary/5' : 'text-slate-700' }}">
                    {{ __('general.nav.history') }}
                </a>

                {{-- Kontakt --}}
                <a href="{{ route('public.contact.index') }}" @wireNavigate
                   class="block font-black uppercase text-xs tracking-widest py-4 px-4 border-b border-slate-50 hover:bg-slate-50 transition-colors {{ request()->routeIs('public.contact.*') ? 'text-primary bg-primary/5' : 'text-slate-700' }}">
                    {{ __('general.nav.contact') }}
                </a>
            </div>
            <a href="{{ Route::has('login') ? route('login') : url('/login') }}" class="btn btn-primary mt-8 py-4 w-full shadow-lg shadow-primary/20">
                <i class="fa-light fa-user-lock mr-2"></i>
                {{ __('nav.login_member') }}
            </a>
        </div>
    </div>
    @endif
</header>
@endCacheFragment
