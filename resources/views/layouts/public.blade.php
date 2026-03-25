<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <meta name="keywords" content="{{ $seo['keywords'] }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">
    <meta name="robots" content="{{ $seo['robots'] }}">

    @include('partials.favicons')

    <!-- Google Tag Manager / Analytics -->
    @if($gaId = env('GA_MEASUREMENT_ID'))
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $gaId }}');
        </script>
    @endif
    @if($gtmId = env('GTM_CONTAINER_ID'))
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','{{ $gtmId }}');</script>
        <!-- End Google Tag Manager -->
    @endif

    <!-- Facebook Pixel -->
    @if($fbPixelId = env('FB_PIXEL_ID'))
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $fbPixelId }}');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $fbPixelId }}&ev=PageView&noscript=1"/></noscript>
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

    <script>
        /**
         * KRITICKÝ FIX: Globální potlačení Livewire 3 abort rejekcí v <head>.
         * Tento skript zachytává "prázdné" rejekce, které Livewire 3 vyhazuje při přerušení
         * asynchronního požadavku (např. při pollingu nebo navigaci).
         */
        (function() {
            var suppress = function(e) {
                var r = e.reason;
                // Livewire 3 abort objekt: { status: null, body: null, json: null, errors: null }
                // Rejekce může být i undefined/null při navigaci.
                var isLivewireAbort = !r || (
                    typeof r === 'object' &&
                    'status' in r && r.status === null &&
                    ('body' in r || 'json' in r || 'errors' in r)
                );
                if (isLivewireAbort) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
            };
            window.addEventListener('unhandledrejection', suppress, true);
        })();
    </script>

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $seo['og_title'] }}">
    <meta property="og:description" content="{{ $seo['og_description'] }}">
    <meta property="og:type" content="{{ $seo['og_type'] }}">
    <meta property="og:url" content="{{ $seo['canonical'] }}">
    <meta property="og:locale" content="{{ $seo['og_locale'] }}">
    @if($seo['og_image'])
        <meta property="og:image" content="{{ $seo['og_image'] }}">
    @endif
    <meta property="og:site_name" content="{{ $seo['site_name'] }}">

    <!-- Twitter / X -->
    <meta name="twitter:card" content="{{ $seo['twitter_card'] }}">
    <meta name="twitter:title" content="{{ $seo['og_title'] }}">
    <meta name="twitter:description" content="{{ $seo['og_description'] }}">
    @if($seo['og_image'])
        <meta name="twitter:image" content="{{ $seo['og_image'] }}">
        <meta name="twitter:image:alt" content="{{ $seo['twitter_image_alt'] }}">
    @endif

    <!-- Structured Data -->
    @foreach($seo['structured_data'] as $schema)
        <script type="application/ld+json">
            {!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endforeach

    <meta name="theme-color" content="{{ $branding['colors']['red'] ?? '#e11d48' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>{!! $branding_css !!}</style>
    <style>
        /* Stabilizace ikon pro zamezení FOUC (problikávání velkých glyfů) */
        .fa-light, .fa-regular, .fa-solid, .fa-brands, .fa-thin, .fa-duotone, .fal, .far, .fas, .fab, .fat, .fad {
            display: inline-block;
            line-height: 1;
            vertical-align: -0.125em;
            opacity: 0;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(isset($head_code))
        {!! $head_code !!}
    @endif

    @stack('head')
    <style>[x-cloak] { display: none !important; }</style>

    <!-- Charts (ApexCharts) -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts" defer></script>
</head>
<body class="min-h-screen flex flex-col bg-slate-50"
      x-data="{ headerHeight: 0, scrolled: false }"
      x-init="headerHeight = $refs.header.offsetHeight; $nextTick(() => headerHeight = $refs.header.offsetHeight)"
      @resize.window="headerHeight = $refs.header.offsetHeight"
      @scroll.window="scrolled = window.scrollY > 10">
    @if($gtmId = env('GTM_CONTAINER_ID'))
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}"
                          height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
    @endif
    <header x-ref="header"
            class="fixed top-0 left-0 w-full z-50 transition-all duration-300"
            :class="{ 'shadow-xl shadow-slate-200/50': scrolled }">
        <x-announcement-bar :announcements="$announcements ?? []" />
        <x-header :branding="$branding ?? []" :navigation="config('navigation.public', [])" />
    </header>

    {{-- Dynamický spacer pro kompenzaci výšky fixního menu --}}
    <div :style="{ height: headerHeight + 'px' }"></div>

    <x-loader-global />

    <!-- Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <x-footer :branding="$branding ?? []" :navigation="config('navigation.public', [])" />

    <x-back-to-top />

    <livewire:sync-status-bar />

    @if(isset($footer_code))
        {!! $footer_code !!}
    @endif

    @stack('scripts')
</body>
</html>
