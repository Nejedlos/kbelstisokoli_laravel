<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>
        <script>
            /**
             * VYNUCENÍ SVĚTLÉHO REŽIMU NA MOBILECH
             * Na zařízeních s šířkou pod 1024px odstraňujeme třídu .dark.
             */
            (function() {
                const forceLightOnMobile = () => {
                    if (window.innerWidth < 1024) {
                        document.documentElement.classList.remove('dark');
                    }
                };
                forceLightOnMobile();
                window.addEventListener('resize', forceLightOnMobile);
                document.addEventListener('livewire:navigated', forceLightOnMobile);
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
