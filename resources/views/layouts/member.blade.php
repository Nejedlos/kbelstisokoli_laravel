<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? __('nav.member_section') }} | {{ $branding['club_name'] ?? 'Kbelští sokoli' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;700&family=Oswald:wght@700&display=swap" rel="stylesheet">

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" integrity="sha512-7iy7fS9870G/9p++Sdf3X56A1lJozX4Lly/6yM3eR92Rpj0yW/S63eBaoOa52pW5Yh1I+O0F0L2M1yW8v8H/2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js" integrity="sha512-9AORv1YPI6/R2D2K4zE6yOq0K/2fW8yW2Z6O/A1A9A+7S9k5m8F8v9A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    @stack('head')
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full flex flex-col antialiased font-sans text-text selection:bg-primary selection:text-white" x-data="{ sidebarOpen: false }">
    <x-announcement-bar :announcements="$announcements ?? []" />

    <!-- Top Bar -->
    <header class="member-topbar sticky top-0 z-30 shadow-md">
        <div class="container-fluid px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <!-- Mobile Menu Trigger -->
                <button @click="sidebarOpen = true" class="lg:hidden p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                    <i class="fa-light fa-bars text-xl"></i>
                </button>

                <!-- Logo -->
                <a href="{{ route('member.dashboard') }}" class="flex items-center gap-3 group">
                    @if($branding['logo_path'] ?? null)
                        <div class="w-10 h-10 bg-white/10 rounded-club flex items-center justify-center p-1.5 transition-transform group-hover:scale-105">
                            <img src="{{ web_asset($branding['logo_path']) }}" class="max-w-full max-h-full object-contain" alt="">
                        </div>
                        <div class="flex flex-col leading-tight hidden sm:flex">
                            <span class="text-sm font-black uppercase tracking-tight">{{ $branding['club_short_name'] ?? 'Sokoli' }}</span>
                            <span class="text-[10px] uppercase tracking-widest text-primary font-bold">{{ __('nav.member_zone') }}</span>
                        </div>
                    @endif
                </a>
            </div>

            <!-- Search & User Menu -->
            <div class="flex items-center gap-2 sm:gap-4 flex-1 justify-end">
                <!-- Language Switcher -->
                <div class="flex items-center gap-1 bg-white/10 p-1 rounded-lg text-[10px] font-black tracking-widest shadow-sm border border-white/10">
                    <a href="{{ route('language.switch', ['lang' => 'cs']) }}"
                       class="px-2.5 py-1 rounded-md transition-all cursor-pointer {{ app()->getLocale() === 'cs' ? 'bg-accent text-white shadow-sm' : 'text-white/50 hover:text-white hover:bg-white/10' }}">
                        CZ
                    </a>
                    <a href="{{ route('language.switch', ['lang' => 'en']) }}"
                       class="px-2.5 py-1 rounded-md transition-all cursor-pointer {{ app()->getLocale() === 'en' ? 'bg-accent text-white shadow-sm' : 'text-white/50 hover:text-white hover:bg-white/10' }}">
                        EN
                    </a>
                </div>

                <!-- Standard Search (Desktop) -->
                <div class="hidden md:block flex-1 max-w-[320px] relative group">
                    <form action="{{ route('member.search') }}" method="GET" class="relative">
                        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-white/40 group-focus-within:text-accent transition-colors">
                            <i class="fa-light fa-magnifying-glass text-[13px]"></i>
                        </div>
                        <input type="text"
                               name="q"
                               placeholder="{{ __('Search') }}..."
                               class="w-full bg-black/20 border border-white/10 rounded-lg pl-9 pr-4 py-1.5 text-[12px] text-white placeholder:text-white/60 focus:bg-black/30 focus:border-primary/50 focus:ring-0 outline-none transition-all shadow-inner">
                    </form>
                </div>

                <!-- AI Search (Desktop) -->
                <div x-data="{ searchOpen: false, loading: false }" class="hidden md:block relative">
                    <x-loader.basketball x-show="loading" x-cloak class="z-[60]" />
                    <button @click="searchOpen = true; $nextTick(() => $refs.searchInput.focus())"
                            class="flex items-center gap-3 px-3 py-1.5 bg-black/20 border border-white/10 rounded-lg text-white hover:bg-black/30 hover:border-white/20 transition-all group text-left shadow-inner">
                        <i class="fa-light fa-sparkles text-primary group-hover:scale-110 transition-transform text-[10px]"></i>
                        <span class="text-[11px] truncate font-bold opacity-80 group-hover:opacity-100 transition-opacity">{{ __('search.ai_hint') }}</span>
                        <span class="ml-auto text-[9px] font-black text-white/20 group-hover:text-primary transition-colors">AI</span>
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

                <!-- AI Search (Mobile Trigger) -->
                <div x-data="{ searchOpen: false, loading: false }" class="md:hidden relative">
                    <x-loader.basketball x-show="loading" x-cloak class="z-[60]" />
                    <div class="flex items-center gap-1">
                        <!-- Standard Search Mobile -->
                        <button @click="searchOpen = 'standard'; $nextTick(() => $refs.searchInputMobileStandard.focus())"
                                class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-full transition-colors focus:outline-none">
                            <i class="fa-light fa-magnifying-glass text-xl"></i>
                        </button>
                        <!-- AI Search Mobile -->
                        <button @click="searchOpen = 'ai'; $nextTick(() => $refs.searchInputMobileAi.focus())"
                                class="p-2 text-white hover:bg-white/10 rounded-full transition-colors focus:outline-none">
                            <i class="fa-light fa-sparkles text-xl"></i>
                        </button>
                    </div>

                    <!-- Standard Search Mobile Overlay -->
                    <div x-show="searchOpen === 'standard'"
                         @click.away="searchOpen = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="fixed inset-x-0 top-16 w-screen bg-white rounded-none shadow-2xl border-t border-slate-100 p-3 z-50 overflow-hidden ring-1 ring-black/5"
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
                         @click.away="searchOpen = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="fixed inset-x-0 top-16 w-screen bg-white rounded-none shadow-2xl border-t border-slate-100 p-3 z-50 overflow-hidden ring-1 ring-black/5"
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
                <a href="{{ route('member.notifications.index') }}" class="relative p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-full transition-colors mr-2">
                    <i class="fa-light fa-bell text-xl"></i>
                    @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                        <span class="absolute top-1.5 right-1.5 w-4 h-4 bg-accent text-white text-[10px] font-black flex items-center justify-center rounded-full border-2 border-secondary animate-pulse">
                            {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                <div class="hidden sm:flex flex-col text-right leading-none">
                    <span class="text-sm font-bold">{{ auth()->user()->name }}</span>
                    <span class="text-[10px] uppercase tracking-widest text-white/50">{{ auth()->user()->roles->pluck('name')->first() ?? __('nav.member') }}</span>
                </div>

                <div class="relative" x-data="{ userOpen: false }">
                    <button @click="userOpen = !userOpen" id="top-bar-avatar" class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-black border-2 border-white/10 hover:border-white/30 transition-all overflow-hidden">
                        @if(auth()->user()->hasMedia('avatar'))
                            <img src="{{ auth()->user()->getFirstMediaUrl('avatar', 'thumb') }}" class="w-full h-full object-cover rounded-full" alt="">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
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
        <aside class="hidden lg:flex w-64 flex-col bg-white border-r border-slate-200 shadow-sm">
            <nav class="flex-1 overflow-y-auto p-4 space-y-1">
                @foreach (config('navigation.member.main', []) as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-club text-sm font-bold transition-all {{ request()->routeIs($item['route']) ? 'bg-primary text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-secondary' }}">
                        @if($item['icon'] ?? null)
                            <x-dynamic-component :component="$item['icon']" class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-400' }}" />
                        @endif
                        {{ __($item['title']) }}
                    </a>
                @endforeach

                @if(auth()->user()->can('manage_teams') && !empty(config('navigation.member.coach')))
                    <div class="mt-8 mb-2 px-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ __('nav.for_coaches') }}</div>
                    @foreach (config('navigation.member.coach', []) as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-club text-sm font-bold transition-all {{ request()->routeIs($item['route']) ? 'bg-secondary text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-secondary' }}">
                            @if($item['icon'] ?? null)
                                <x-dynamic-component :component="$item['icon']" class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-400' }}" />
                            @endif
                            {{ __($item['title']) }}
                        </a>
                    @endforeach
                @endif
            </nav>

            <!-- Bottom Action / Help -->
            <div class="p-4 border-t border-slate-100">
                <a href="{{ route('public.contact.index') }}" class="block p-4 rounded-club bg-slate-50 text-xs text-slate-500 hover:bg-slate-100 transition-colors">
                    <span class="font-bold text-slate-700 block mb-1">{{ __('nav.need_help') }}</span>
                    {{ __('nav.help_text') }}
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
        <main class="flex-1 overflow-y-auto overflow-x-hidden">
            <!-- Breadcrumbs / Page Header -->
            <div class="bg-white border-b border-slate-200 py-4 px-4 sm:px-8">
                <div class="max-w-6xl mx-auto flex items-center justify-between">
                    <div>
                        <h1 class="text-xl md:text-2xl font-black uppercase tracking-tight text-secondary">
                            {{ $title ?? __('nav.member_section') }}
                        </h1>
                        @if(isset($subtitle))
                            <p class="text-xs md:text-sm text-slate-500 font-medium">{{ $subtitle }}</p>
                        @endif
                    </div>

                    <!-- Quick Action (Desktop) -->
                    @yield('header_actions')
                </div>
            </div>

            <!-- Page Content -->
            <div class="py-8 px-4 sm:px-8">
                <div class="max-w-6xl mx-auto">
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
    <nav class="lg:hidden h-20 bg-white border-t border-slate-100 flex items-center justify-around px-2 z-30 shadow-[0_-8px_30px_rgba(0,0,0,0.04)] relative">
        <span class="absolute top-0 left-0 right-0 brand-stripe"></span>
        <a href="{{ route('member.dashboard') }}" class="flex flex-col items-center gap-1.5 {{ request()->routeIs('member.dashboard') ? 'text-primary' : 'text-slate-400' }} transition-colors duration-300">
            <i class="fa-light fa-grid-2 text-xl"></i>
            <span class="text-[9px] font-black uppercase tracking-widest">{{ __('nav.dashboard') }}</span>
        </a>
        <a href="{{ route('member.attendance.index') }}" class="flex flex-col items-center gap-1.5 {{ request()->routeIs('member.attendance.*') ? 'text-primary' : 'text-slate-400' }} transition-colors duration-300">
            <i class="fa-light fa-calendar-star text-xl"></i>
            <span class="text-[9px] font-black uppercase tracking-widest">{{ __('nav.program') }}</span>
        </a>
        <a href="{{ route('member.profile.edit') }}" class="flex flex-col items-center gap-1.5 {{ request()->routeIs('member.profile.*') ? 'text-primary' : 'text-slate-400' }} transition-colors duration-300">
            <i class="fa-light fa-user-gear text-xl"></i>
            <span class="text-[9px] font-black uppercase tracking-widest">{{ __('nav.profile') }}</span>
        </a>
        <button @click="sidebarOpen = true" class="flex flex-col items-center gap-1.5 text-slate-400 hover:text-primary transition-colors duration-300">
            <i class="fa-light fa-circle-nodes text-xl"></i>
            <span class="text-[9px] font-black uppercase tracking-widest">{{ __('nav.more') }}</span>
        </button>
    </nav>

    <x-back-to-top />
    @stack('scripts')
</body>
</html>
