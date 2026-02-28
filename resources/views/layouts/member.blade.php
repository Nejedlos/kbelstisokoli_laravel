<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>{{ $title ?? __('nav.member_section') }} | {{ $branding['club_name'] ?? 'Kbelští sokoli' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

    <style>{!! $branding_css !!}</style>
    <style>
        /* Stabilizace ikon pro zamezení FOUC (problikávání velkých glyfů) */
        .fa-light, .fa-regular, .fa-solid, .fa-brands, .fa-thin, .fa-duotone, .fal, .far, .fas, .fab, .fat, .fad {
            display: inline-block;
            width: 1.25em;
            height: 1em;
            line-height: 1;
            vertical-align: -0.125em;
            overflow: hidden;
            opacity: 0;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" integrity="sha512-hvNR0F/e2J7zPPfLC9auFe3/SE0yG4aJCOd/qxew74NN7eyiSKjr7xJJMu1Jy2wf7FXITpWS1E/RY8yzuXN7VA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js" integrity="sha512-9KkIqdfN7ipEW6B6k+Aq20PV31bjODg4AA52W+tYtAE0jE0kMx49bjJ3FgvS56wzmyfMUHbQ4Km2b7l9+Y/+Eg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    @stack('head')
    <style>[x-cloak] { display: none !important; }</style>
    @livewireStyles
</head>
<body class="h-full flex flex-col antialiased font-sans text-text selection:bg-primary selection:text-white bg-slate-50/50" x-data="{ sidebarOpen: false }">
    <x-impersonation-banner />
    <x-impersonation-notification />
    <x-announcement-bar :announcements="$announcements ?? []" />

    <!-- Top Bar -->
    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/60 shadow-sm h-18">
        <div class="container-fluid px-4 sm:px-6 md:px-8 h-full flex items-center justify-between">
            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                <!-- Mobile Menu Trigger -->
                <button @click="sidebarOpen = true" class="lg:hidden p-2 text-slate-500 hover:text-primary hover:bg-primary/5 rounded-xl transition-all min-w-[44px] min-h-[44px] flex items-center justify-center">
                    <i class="fa-light fa-bars-staggered text-xl"></i>
                </button>

                <!-- Logo -->
                <a href="{{ route('member.dashboard') }}" class="flex items-center gap-2 sm:gap-3 group">
                    @if($branding['logo_path'] ?? null)
                        <div class="w-10 h-10 sm:w-11 sm:h-11 bg-slate-50 rounded-xl sm:rounded-2xl flex items-center justify-center p-1.5 sm:p-2 transition-all group-hover:scale-105 group-hover:shadow-lg group-hover:shadow-primary/5 group-hover:bg-white border border-slate-100">
                            <img src="{{ web_asset($branding['logo_path']) }}" class="max-w-full max-h-full object-contain" alt="">
                        </div>
                        <div class="flex flex-col leading-tight hidden xs:flex sm:flex">
                            <span class="text-[12px] sm:text-sm font-black uppercase tracking-tight text-secondary group-hover:text-primary transition-colors">{{ $branding['club_short_name'] ?? 'Sokoli' }}</span>
                            <span class="text-[9px] sm:text-[10px] uppercase tracking-[0.15em] sm:tracking-[0.2em] text-slate-400 font-bold group-hover:text-slate-600 transition-colors">{{ __('nav.member_zone') }}</span>
                        </div>
                    @endif
                </a>
            </div>

            <!-- Search & User Menu -->
            <div class="flex items-center gap-2 sm:gap-4 md:gap-5 flex-1 justify-end">
                <!-- Language Switcher (Desktop) -->
                <div class="hidden sm:flex items-center gap-1 bg-slate-100/80 p-1 rounded-xl text-[10px] font-black tracking-widest border border-slate-200/50">
                    <a href="{{ route('language.switch', ['lang' => 'cs']) }}"
                       class="px-2.5 py-1.5 rounded-lg transition-all cursor-pointer {{ app()->getLocale() === 'cs' ? 'bg-white text-primary shadow-sm ring-1 ring-slate-200' : 'text-slate-400 hover:text-slate-600' }}">
                        CZ
                    </a>
                    <a href="{{ route('language.switch', ['lang' => 'en']) }}"
                       class="px-2.5 py-1.5 rounded-lg transition-all cursor-pointer {{ app()->getLocale() === 'en' ? 'bg-white text-primary shadow-sm ring-1 ring-slate-200' : 'text-slate-400 hover:text-slate-600' }}">
                        EN
                    </a>
                </div>

                <!-- Standard Search (Desktop) -->
                <div class="hidden lg:block flex-1 max-w-[240px] xl:max-w-[320px] relative group">
                    <form action="{{ route('member.search') }}" method="GET" class="relative">
                        <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                            <i class="fa-light fa-magnifying-glass text-sm"></i>
                        </div>
                        <input type="text"
                               name="q"
                               placeholder="{{ __('Search') }}..."
                               class="w-full bg-slate-100/50 border border-slate-200/60 rounded-xl pl-10 pr-4 py-2 text-[12px] text-secondary placeholder:text-slate-400 focus:bg-white focus:border-primary/30 focus:ring-4 focus:ring-primary/5 outline-none transition-all">
                    </form>
                </div>

                <!-- AI Search (Desktop/Tablet) -->
                <div x-data="{ searchOpen: false, loading: false }" class="hidden lg:block relative">
                    <x-loader.basketball x-show="loading" x-cloak class="z-[60]" />
                    <button @click="searchOpen = true; $nextTick(() => $refs.searchInput.focus())"
                            class="flex items-center gap-3 px-3 xl:px-4 py-2 bg-slate-900 text-white rounded-xl hover:bg-secondary transition-all group text-left shadow-lg shadow-slate-200 min-h-[40px]">
                        <i class="fa-light fa-sparkles text-primary group-hover:scale-110 group-hover:rotate-12 transition-transform text-[11px]"></i>
                        <span class="text-[11px] truncate font-black uppercase tracking-widest hidden xl:inline">{{ __('search.ai_hint') }}</span>
                        <span class="text-[11px] font-black uppercase tracking-widest xl:hidden">AI Search</span>
                        <div class="ml-auto flex items-center gap-1.5">
                            <span class="w-px h-3 bg-white/20"></span>
                            <span class="text-[9px] font-black opacity-40 group-hover:text-primary transition-colors">AI</span>
                        </div>
                    </button>

                    <div x-show="searchOpen"
                         @click.away="searchOpen = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="absolute right-0 mt-3 w-full min-w-[380px] bg-white rounded-2xl shadow-2xl border border-slate-100 p-4 z-50 overflow-hidden ring-1 ring-black/5"
                         style="display: none;">

                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-2 h-2 rounded-full bg-primary shadow-[0_0_8px_rgba(225,29,72,0.5)]"></div>
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-900">{{ __('search.ai_suggestion') }}</h3>
                        </div>

                        <form action="{{ route('member.ai') }}" method="GET" class="relative" @submit.prevent="loading = true; window.location.href = '{{ route('member.ai') }}?q=' + encodeURIComponent($refs.searchInput.value)">
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-light fa-sparkles text-sm animate-pulse text-primary"></i>
                            </div>
                            <input type="text"
                                   name="q"
                                   x-ref="searchInput"
                                   placeholder="{{ __('search.ai_search_placeholder') }}"
                                   class="w-full bg-slate-100 border-2 border-slate-200 rounded-xl pl-10 pr-12 py-3 text-sm text-slate-700 focus:border-primary focus:ring-0 outline-none transition-all">
                            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg bg-primary text-white flex items-center justify-center hover:scale-105 transition-transform">
                                <i class="fa-light fa-arrow-right text-xs"></i>
                            </button>
                        </form>

                        <div class="mt-4 space-y-3">
                            <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest flex items-center gap-2">
                                <span class="w-4 h-px bg-slate-100"></span>
                                {{ __('search.ai_try_asking') }}
                                <span class="flex-1 h-px bg-slate-100"></span>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach(['omluva', 'zapas', 'platba', 'heslo'] as $key)
                                    <button @click.stop="loading = true; $refs.searchInput.value = '{{ __('search.ai_tips.' . $key) }}'; $refs.searchInput.form.dispatchEvent(new Event('submit'))"
                                            class="flex items-start gap-2 text-left p-2.5 rounded-xl bg-slate-50 hover:bg-accent/5 border border-slate-100 hover:border-accent/20 transition-all group/tip">
                                        <div class="w-5 h-5 rounded bg-white flex items-center justify-center shadow-sm text-[10px] text-accent group-hover/tip:bg-accent group-hover/tip:text-white transition-colors">
                                            <i class="fa-light @if($key === 'omluva') fa-calendar-xmark @elseif($key === 'zapas') fa-basketball @elseif($key === 'platba') fa-wallet @else fa-key @endif"></i>
                                        </div>
                                        <span class="text-[11px] font-bold text-slate-700 leading-tight">
                                            {{ __('search.ai_tips.' . $key) }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search (Mobile/Tablet Trigger) -->
                <div x-data="{ searchOpen: null, loading: false }" class="lg:hidden flex items-center">
                    <x-loader.basketball x-show="loading" x-cloak class="z-[60]" />
                    <div class="flex items-center">
                        <!-- Standard Search Mobile -->
                        <button @click="searchOpen = (searchOpen === 'standard' ? null : 'standard'); if(searchOpen === 'standard') $nextTick(() => $refs.searchInputMobileStandard.focus())"
                                class="p-2 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all min-w-[40px] min-h-[40px] flex items-center justify-center"
                                :class="{ 'bg-primary/5 text-primary': searchOpen === 'standard' }">
                            <i class="fa-light fa-magnifying-glass text-xl"></i>
                        </button>

                        <!-- AI Search Mobile -->
                        <button @click="searchOpen = (searchOpen === 'ai' ? null : 'ai'); if(searchOpen === 'ai') $nextTick(() => $refs.searchInputMobileAi.focus())"
                                class="p-2 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all min-w-[40px] min-h-[40px] flex items-center justify-center"
                                :class="{ 'bg-primary/5 text-primary': searchOpen === 'ai' }">
                            <i class="fa-light fa-sparkles text-xl"></i>
                        </button>
                    </div>

                    <!-- Standard Search Mobile Overlay -->
                    <div x-show="searchOpen === 'standard'"
                         @click.away="searchOpen = null"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="fixed inset-x-0 top-18 w-screen bg-white shadow-2xl border-t border-slate-100 p-3 z-50 overflow-hidden"
                         style="display: none;">
                        <form action="{{ route('member.search') }}" method="GET" class="relative">
                            <input type="text"
                                   name="q"
                                   x-ref="searchInputMobileStandard"
                                   placeholder="{{ __('Search') }}..."
                                   class="w-full bg-slate-100 border-2 border-slate-200 rounded-xl px-4 py-2 text-sm text-slate-700 focus:border-primary focus:ring-0 outline-none pr-10">
                            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-primary">
                                <i class="fa-light fa-arrow-right text-sm"></i>
                            </button>
                        </form>
                    </div>

                    <!-- AI Search Mobile Overlay -->
                    <div x-show="searchOpen === 'ai'"
                         @click.away="searchOpen = null"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="fixed inset-x-0 top-18 w-screen bg-white shadow-2xl border-t border-slate-100 p-3 z-50 overflow-hidden"
                         style="display: none;">
                        <form action="{{ route('member.ai') }}" method="GET" class="relative" @submit.prevent="loading = true; window.location.href = '{{ route('member.ai') }}?q=' + encodeURIComponent($refs.searchInputMobileAi.value)">
                            <input type="text"
                                   name="q"
                                   x-ref="searchInputMobileAi"
                                   placeholder="{{ __('search.ai_hint') }}"
                                   class="w-full bg-slate-100 border-2 border-slate-200 rounded-xl px-4 py-2 text-sm text-slate-700 focus:border-primary focus:ring-0 outline-none pr-10">
                            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-primary">
                                <i class="fa-light fa-arrow-right text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Notifications -->
                <a href="{{ route('member.notifications.index') }}" class="relative p-2.5 text-slate-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all min-w-[44px] min-h-[44px] flex items-center justify-center">
                    <i class="fa-light fa-bell text-xl"></i>
                    @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                        <span class="absolute top-2.5 right-2.5 w-4.5 h-4.5 bg-primary text-white text-[9px] font-black flex items-center justify-center rounded-full border-2 border-white shadow-sm animate-pulse">
                            {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                <div class="hidden md:flex flex-col text-right leading-none gap-0.5">
                    <span class="text-sm font-black text-secondary tracking-tight">{{ auth()->user()->name }}</span>
                    <span class="text-[9px] font-bold uppercase tracking-[0.15em] text-slate-400">{{ auth()->user()->roles->pluck('name')->first() ?? __('nav.member') }}</span>
                </div>

                <div class="relative" x-data="{ userOpen: false }">
                    <button @click="userOpen = !userOpen" id="top-bar-avatar" class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-slate-100 flex items-center justify-center text-secondary font-black border border-slate-200 hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5 transition-all overflow-hidden group min-w-[40px] min-h-[40px]">
                        <img src="{{ auth()->user()->getAvatarUrl('thumb') }}" class="w-full h-full object-cover transition-transform group-hover:scale-110" alt="">
                    </button>

                    <div x-show="userOpen" @click.away="userOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-club shadow-xl border border-slate-100 py-2 text-slate-700 z-50">
                        <a href="{{ route('member.profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-slate-50 transition-colors font-bold">{{ __('nav.my_profile') }}</a>
                        @if(auth()->user()?->canAccessAdmin())
                            <a href="{{ url(config('filament.panels.admin.path', 'admin')) }}" class="block px-4 py-2 text-sm text-accent hover:bg-accent/5 transition-colors font-bold">
                                <i class="fa-light fa-shield-check mr-2"></i> {{ __('nav.administration') }}
                            </a>
                        @endif
                        <a href="{{ route('public.home') }}" class="block px-4 py-2 text-sm hover:bg-slate-50 transition-colors">{{ __('nav.public_web') }}</a>
                        <hr class="my-2 border-slate-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-danger-600 hover:bg-danger-50 transition-colors font-bold">{{ __('nav.logout') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">
        <!-- Sidebar Navigation (Desktop) -->
        <aside class="hidden lg:flex w-72 flex-col bg-white border-r border-slate-200/60 relative z-20">
            <nav class="flex-1 overflow-y-auto p-6 space-y-1.5 custom-scrollbar">
                @foreach (config('navigation.member.main', []) as $item)
                    <a href="{{ route($item['route']) }}"
                       class="group flex items-center gap-3.5 px-4 py-3 rounded-2xl text-[13px] font-bold transition-all {{ request()->routeIs($item['route']) ? 'bg-primary text-white shadow-xl shadow-primary/20 scale-[1.02]' : 'text-slate-500 hover:bg-slate-50 hover:text-secondary' }}">
                        <div class="w-8 h-8 flex items-center justify-center rounded-xl transition-colors {{ request()->routeIs($item['route']) ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-white group-hover:shadow-sm group-hover:text-primary' }}">
                            @if($item['icon'] ?? null)
                                <x-dynamic-component :component="$item['icon']" class="w-4 h-4" />
                            @endif
                        </div>
                        <span class="uppercase tracking-widest text-[11px]">{{ __($item['title']) }}</span>
                        @if(request()->routeIs($item['route']))
                            <i class="fa-light fa-chevron-right ml-auto text-[10px] opacity-50"></i>
                        @endif
                    </a>
                @endforeach

                @if(auth()->user()->can('manage_teams') && !empty(config('navigation.member.coach')))
                    <div class="pt-8 pb-3 px-4 flex items-center gap-3">
                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400 whitespace-nowrap">{{ __('nav.for_coaches') }}</span>
                        <div class="h-px bg-slate-100 flex-1"></div>
                    </div>
                    @foreach (config('navigation.member.coach', []) as $item)
                        <a href="{{ route($item['route']) }}"
                           class="group flex items-center gap-3.5 px-4 py-3 rounded-2xl text-[13px] font-bold transition-all {{ request()->routeIs($item['route']) ? 'bg-secondary text-white shadow-xl shadow-secondary/20 scale-[1.02]' : 'text-slate-500 hover:bg-slate-50 hover:text-secondary' }}">
                            <div class="w-8 h-8 flex items-center justify-center rounded-xl transition-colors {{ request()->routeIs($item['route']) ? 'bg-white/10' : 'bg-slate-100 group-hover:bg-white group-hover:shadow-sm group-hover:text-primary' }}">
                                @if($item['icon'] ?? null)
                                    <x-dynamic-component :component="$item['icon']" class="w-4 h-4" />
                                @endif
                            </div>
                            <span class="uppercase tracking-widest text-[11px]">{{ __($item['title']) }}</span>
                        </a>
                    @endforeach
                @endif
            </nav>

            <!-- Bottom Action / Help -->
            <div class="p-6 border-t border-slate-100 bg-slate-50/30">
                <a href="{{ route('public.contact.index') }}" class="group block p-5 rounded-2xl bg-white border border-slate-200/60 shadow-sm hover:shadow-md hover:border-primary/20 transition-all">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fa-light fa-headset text-sm"></i>
                        </div>
                        <span class="font-black text-secondary text-[11px] uppercase tracking-wider">{{ __('nav.need_help') }}</span>
                    </div>
                    <p class="text-[10px] text-slate-500 leading-relaxed font-medium">
                        {{ __('nav.help_text') }}
                    </p>
                </a>
            </div>
        </aside>

        <!-- Sidebar Mobile Overlay -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-40 lg:hidden"
             @click="sidebarOpen = false"></div>

        <!-- Sidebar Mobile Content -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 w-72 bg-white z-50 lg:hidden shadow-2xl flex flex-col">
            <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100">
                <span class="font-black uppercase tracking-tight text-secondary">{{ __('nav.section_menu') }}</span>
                <button @click="sidebarOpen = false" class="p-2 text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fa-light fa-xmark text-2xl"></i>
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto p-6 space-y-2">
                @foreach (config('navigation.member.main', []) as $item)
                    <a href="{{ route($item['route']) }}"
                       @click="sidebarOpen = false"
                       class="flex items-center gap-4 px-4 py-4 rounded-club text-base font-bold transition-all {{ request()->routeIs($item['route']) ? 'bg-primary text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50' }}">
                        @if($item['icon'] ?? null)
                            <x-dynamic-component :component="$item['icon']" class="w-6 h-6 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-400' }}" />
                        @endif
                        {{ __($item['title']) }}
                    </a>
                @endforeach

                @if(auth()->user()->can('manage_teams'))
                    <div class="mt-8 mb-2 px-4 text-xs font-black uppercase tracking-[0.2em] text-slate-400">{{ __('nav.for_coaches') }}</div>
                    @foreach (config('navigation.member.coach', []) as $item)
                        <a href="{{ route($item['route']) }}"
                           @click="sidebarOpen = false"
                           class="flex items-center gap-4 px-4 py-4 rounded-club text-base font-bold transition-all {{ request()->routeIs($item['route']) ? 'bg-secondary text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50' }}">
                            @if($item['icon'] ?? null)
                                <x-dynamic-component :component="$item['icon']" class="w-6 h-6 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-400' }}" />
                            @endif
                            {{ __($item['title']) }}
                        </a>
                    @endforeach
                @endif
            </nav>
        </div>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto overflow-x-hidden bg-slate-50/50">
            <!-- Breadcrumbs / Page Header -->
            <div class="bg-white/40 backdrop-blur-sm border-b border-slate-200/60 py-6 px-4 sm:px-6 md:px-10">
                <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">
                            <a href="{{ route('member.dashboard') }}" class="hover:text-primary transition-colors">{{ __('nav.dashboard') }}</a>
                            <i class="fa-light fa-chevron-right text-[8px]"></i>
                            <span class="text-slate-300 italic">{{ $title ?? '' }}</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-black uppercase tracking-tight text-secondary leading-none">
                            {{ $title ?? __('nav.member_section') }}
                        </h1>
                        @if(isset($subtitle))
                            <p class="text-[11px] sm:text-[13px] md:text-sm text-slate-500 font-medium italic opacity-80 leading-relaxed">{{ $subtitle }}</p>
                        @endif
                    </div>

                    <!-- Header Actions (Desktop) -->
                    <div class="flex items-center gap-2 sm:gap-3">
                        @yield('header_actions')
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div class="py-6 sm:py-8 md:py-10 px-4 sm:px-6 md:px-10">
                <div class="max-w-7xl mx-auto">
                    @if (session('status'))
                        <div class="mb-8 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 rounded-2xl flex items-center gap-4 animate-fade-in">
                            <i class="fa-light fa-circle-check text-emerald-500 text-lg"></i>
                            <span class="font-bold text-sm">{{ session('status') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-8 p-4 bg-rose-500/10 border border-rose-500/20 text-rose-700 rounded-2xl flex items-center gap-4">
                            <i class="fa-light fa-circle-exclamation text-rose-500 text-lg"></i>
                            <span class="font-bold text-sm">{{ session('error') }}</span>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <!-- Bottom Navigation (Mobile Only) -->
    <nav class="lg:hidden h-20 bg-white/90 backdrop-blur-xl border-t border-slate-200/60 flex items-center justify-around px-2 z-30 shadow-[0_-10px_40px_rgba(0,0,0,0.08)] relative">
        <span class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-accent to-primary opacity-50"></span>
        <a href="{{ route('member.dashboard') }}" class="flex flex-col items-center justify-center gap-1 p-2 rounded-2xl transition-all duration-300 min-w-[64px] min-h-[64px] {{ request()->routeIs('member.dashboard') ? 'text-primary bg-primary/5' : 'text-slate-400' }}">
            <i class="fa-light fa-grid-2 text-xl"></i>
            <span class="text-[8px] font-black uppercase tracking-[0.2em]">{{ __('nav.dashboard') }}</span>
        </a>
        <a href="{{ route('member.attendance.index') }}" class="flex flex-col items-center justify-center gap-1 p-2 rounded-2xl transition-all duration-300 min-w-[64px] min-h-[64px] {{ request()->routeIs('member.attendance.*') ? 'text-primary bg-primary/5' : 'text-slate-400' }}">
            <i class="fa-light fa-calendar-star text-xl"></i>
            <span class="text-[8px] font-black uppercase tracking-[0.2em]">{{ __('nav.program') }}</span>
        </a>
        <a href="{{ route('member.profile.edit') }}" class="flex flex-col items-center justify-center gap-1 p-2 rounded-2xl transition-all duration-300 min-w-[64px] min-h-[64px] {{ request()->routeIs('member.profile.*') ? 'text-primary bg-primary/5' : 'text-slate-400' }}">
            <i class="fa-light fa-user-gear text-xl"></i>
            <span class="text-[8px] font-black uppercase tracking-[0.2em]">{{ __('nav.profile') }}</span>
        </a>
        <button @click="sidebarOpen = true" class="flex flex-col items-center justify-center gap-1 p-2 rounded-2xl text-slate-400 hover:text-primary transition-all duration-300 min-w-[64px] min-h-[64px]">
            <i class="fa-light fa-circle-nodes text-xl"></i>
            <span class="text-[8px] font-black uppercase tracking-[0.2em]">{{ __('nav.more') }}</span>
        </button>
    </nav>

    <x-back-to-top />
    <script>
        window.addEventListener('avatarUpdated', (event) => {
            const data = event.detail;
            console.log('Avatar updated event received:', data);
            const topAvatar = document.getElementById('top-bar-avatar');
            const profileAvatar = document.getElementById('avatarPreview');
            const deleteBtn = document.getElementById('avatar-delete-btn');

            if (topAvatar) {
                if (data.url) {
                    topAvatar.innerHTML = `<img src='${data.url}' class='w-full h-full object-cover rounded-full'>`;
                } else {
                    topAvatar.innerHTML = `<div class='w-full h-full flex items-center justify-center bg-primary text-white font-black'>${data.initials}</div>`;
                }
            }

            if (profileAvatar) {
                profileAvatar.src = data.url || 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';
            }

            if (deleteBtn) {
                if (data.url) {
                    deleteBtn.classList.remove('hidden');
                } else {
                    deleteBtn.classList.add('hidden');
                }
            }
        });
    </script>
    @stack('scripts')
    @livewireScripts
</body>
</html>
