<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>
        <meta name="color-scheme" content="light">
        <script>
            /**
             * ABSOLUTNÍ VYNUCENÍ SVĚTLÉHO REŽIMU
             * Odstraňujeme třídu .dark z dokumentu, aby aplikace vypadala vždy světle
             * bez ohledu na systémové nastavení nebo šířku zařízení.
             */
            (function() {
                const forceLight = () => {
                    document.documentElement.classList.remove('dark');
                    if (localStorage.getItem('theme') === 'dark') {
                        localStorage.setItem('theme', 'light');
                    }
                };
                forceLight();
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

        <x-screenshot.styles />
        <x-screenshot.scripts />

        @livewireStyles
    </head>
    <body>
        {{ $slot }}

        @livewireScripts
    </body>
</html>
