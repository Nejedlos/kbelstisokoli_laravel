<!DOCTYPE html>
<html lang="cs" class="h-full bg-bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Členská sekce' }} | {{ $branding['club_name'] ?? 'Kbelští sokoli' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;700&family=Oswald:wght@700&display=swap" rel="stylesheet">

    <style>{!! $branding_css !!}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="h-full flex flex-col antialiased font-sans text-text selection:bg-primary selection:text-white" x-data="{ sidebarOpen: false }">
    <x-announcement-bar :announcements="$announcements ?? []" />

    <!-- Top Bar -->
    <header class="bg-secondary text-white sticky top-0 z-30 shadow-md">
        <div class="container-fluid px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <!-- Mobile Menu Trigger -->
                <button @click="sidebarOpen = true" class="lg:hidden p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Logo -->
                <a href="{{ route('member.dashboard') }}" class="flex items-center gap-3 group">
                    @if($branding['logo_path'] ?? null)
                        <div class="w-10 h-10 bg-white/10 rounded-club flex items-center justify-center p-1.5 transition-transform group-hover:scale-105">
                            <img src="{{ asset('storage/' . $branding['logo_path']) }}" class="max-w-full max-h-full object-contain" alt="">
                        </div>
                        <div class="flex flex-col leading-tight hidden sm:flex">
                            <span class="text-sm font-black uppercase tracking-tight">{{ $branding['club_short_name'] ?? 'Sokoli' }}</span>
                            <span class="text-[10px] uppercase tracking-widest text-primary font-bold">Členská zóna</span>
                        </div>
                    @endif
                </a>
            </div>

            <!-- User Menu -->
            <div class="flex items-center gap-2 sm:gap-4">
                <!-- Notifications -->
                <a href="{{ route('member.notifications.index') }}" class="relative p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-full transition-colors mr-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
                        <span class="absolute top-1.5 right-1.5 w-4 h-4 bg-primary text-white text-[10px] font-black flex items-center justify-center rounded-full border-2 border-secondary animate-pulse">
                            {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                        </span>
                    @endif
                </a>

                <div class="hidden sm:flex flex-col text-right leading-none">
                    <span class="text-sm font-bold">{{ auth()->user()->name }}</span>
                    <span class="text-[10px] uppercase tracking-widest text-white/50">{{ auth()->user()->roles->pluck('name')->first() ?? 'Člen' }}</span>
                </div>

                <div class="relative" x-data="{ userOpen: false }">
                    <button @click="userOpen = !userOpen" class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-black border-2 border-white/10 hover:border-white/30 transition-all">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </button>

                    <div x-show="userOpen" @click.away="userOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-club shadow-xl border border-slate-100 py-2 text-slate-700 z-50">
                        <a href="{{ route('member.profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-slate-50 transition-colors font-bold">Můj profil</a>
                        <a href="{{ route('public.home') }}" class="block px-4 py-2 text-sm hover:bg-slate-50 transition-colors">Veřejný web</a>
                        <hr class="my-2 border-slate-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-danger-600 hover:bg-danger-50 transition-colors font-bold">Odhlásit se</button>
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
                        {{ $item['title'] }}
                    </a>
                @endforeach

                @if(auth()->user()->can('manage_teams') && !empty(config('navigation.member.coach')))
                    <div class="mt-8 mb-2 px-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Pro trenéry</div>
                    @foreach (config('navigation.member.coach', []) as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-club text-sm font-bold transition-all {{ request()->routeIs($item['route']) ? 'bg-secondary text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-secondary' }}">
                            @if($item['icon'] ?? null)
                                <x-dynamic-component :component="$item['icon']" class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-400' }}" />
                            @endif
                            {{ $item['title'] }}
                        </a>
                    @endforeach
                @endif
            </nav>

            <!-- Bottom Action / Help -->
            <div class="p-4 border-t border-slate-100">
                <a href="{{ route('public.contact.index') }}" class="block p-4 rounded-club bg-slate-50 text-xs text-slate-500 hover:bg-slate-100 transition-colors">
                    <span class="font-bold text-slate-700 block mb-1">Potřebujete pomoc?</span>
                    Máte problém s přístupem nebo profilem? Kontaktujte nás.
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
                <span class="font-black uppercase tracking-tight text-secondary">Menu sekce</span>
                <button @click="sidebarOpen = false" class="p-2 text-slate-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
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
                        {{ $item['title'] }}
                    </a>
                @endforeach

                @if(auth()->user()->can('manage_teams'))
                    <div class="mt-8 mb-2 px-4 text-xs font-black uppercase tracking-[0.2em] text-slate-400">Pro trenéry</div>
                    @foreach (config('navigation.member.coach', []) as $item)
                        <a href="{{ route($item['route']) }}"
                           @click="sidebarOpen = false"
                           class="flex items-center gap-4 px-4 py-4 rounded-club text-base font-bold transition-all {{ request()->routeIs($item['route']) ? 'bg-secondary text-white shadow-lg' : 'text-slate-600 hover:bg-slate-50' }}">
                            @if($item['icon'] ?? null)
                                <x-dynamic-component :component="$item['icon']" class="w-6 h-6 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-400' }}" />
                            @endif
                            {{ $item['title'] }}
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
                            {{ $title ?? 'Členská sekce' }}
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
                        <div class="mb-8 p-4 bg-success-50 border border-success-200 text-success-700 rounded-club flex items-center gap-3 animate-fade-in">
                            <svg class="w-5 h-5 text-success-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="font-bold text-sm">{{ session('status') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-8 p-4 bg-danger-50 border border-danger-200 text-danger-700 rounded-club flex items-center gap-3">
                            <svg class="w-5 h-5 text-danger-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-bold text-sm">{{ session('error') }}</span>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <!-- Bottom Navigation (Mobile Only) -->
    <nav class="lg:hidden h-16 bg-white border-t border-slate-200 flex items-center justify-around px-2 z-30 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
        <a href="{{ route('member.dashboard') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('member.dashboard') ? 'text-primary' : 'text-slate-400' }}">
            <x-heroicon-o-home class="w-6 h-6" />
            <span class="text-[10px] font-black uppercase tracking-widest">Domů</span>
        </a>
        <a href="{{ route('member.attendance.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('member.attendance.*') ? 'text-primary' : 'text-slate-400' }}">
            <x-heroicon-o-calendar-days class="w-6 h-6" />
            <span class="text-[10px] font-black uppercase tracking-widest">Akce</span>
        </a>
        <a href="{{ route('member.profile.edit') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('member.profile.*') ? 'text-primary' : 'text-slate-400' }}">
            <x-heroicon-o-user class="w-6 h-6" />
            <span class="text-[10px] font-black uppercase tracking-widest">Profil</span>
        </a>
        <button @click="sidebarOpen = true" class="flex flex-col items-center gap-1 text-slate-400">
            <x-heroicon-o-bars-3 class="w-6 h-6" />
            <span class="text-[10px] font-black uppercase tracking-widest">Více</span>
        </button>
    </nav>

    @stack('scripts')
</body>
</html>
