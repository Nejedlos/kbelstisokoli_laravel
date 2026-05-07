<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    <title>{{ __('nav.administration') }} - {{ $branding['club_name'] ?? 'Kbelští sokoli' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">
    <script>
        /**
         * ABSOLUTNÍ VYNUCENÍ SVĚTLÉHO REŽIMU (AGRESIVNÍ)
         * Tento skript aktivně sleduje změny na <html> elementu a okamžitě
         * odstraňuje třídu .dark, pokud se ji jakýkoliv jiný skript pokusí přidat.
         */
        (function() {
            const forceLight = () => {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                }
                if (localStorage.getItem('theme') !== 'light') {
                    localStorage.setItem('theme', 'light');
                }
                document.documentElement.style.colorScheme = 'light';
            };
            forceLight();
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        if (document.documentElement.classList.contains('dark')) {
                            document.documentElement.classList.remove('dark');
                        }
                    }
                });
            });
            observer.observe(document.documentElement, { attributes: true });
            window.addEventListener('resize', forceLight);
            document.addEventListener('livewire:navigated', forceLight);
        })();
    </script>
    <!-- Google Tag Manager / Analytics -->
    @if($gaId = config('services.google.analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}

            // Výchozí nastavení souhlasu (Consent Mode v2)
            gtag('consent', 'default', {
                'analytics_storage': 'granted',
                'ad_storage': 'granted',
                'ad_user_data': 'granted',
                'ad_personalization': 'granted',
                'wait_for_update': 500
            });

            gtag('js', new Date());
            gtag('config', '{{ $gaId }}', {
                'send_page_view': true
            });

            // Podpora pro Livewire 3 navigaci (SPA mode)
            document.addEventListener('livewire:navigated', function() {
                if (typeof gtag === 'function') {
                    gtag('config', '{{ $gaId }}', {
                        'page_path': window.location.pathname,
                        'page_location': window.location.href,
                        'page_title': document.title
                    });
                }
            });
        </script>
    @endif

    @vite(['resources/css/icons-fix.css', 'resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header>
        <nav>
            <ul>
                @foreach (config('navigation.admin', []) as $item)
                    <li>
                        <a href="{{ route($item['route']) }}">{{ __($item['title']) }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
