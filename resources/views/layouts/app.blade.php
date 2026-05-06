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
