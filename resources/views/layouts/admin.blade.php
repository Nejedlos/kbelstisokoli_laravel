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
