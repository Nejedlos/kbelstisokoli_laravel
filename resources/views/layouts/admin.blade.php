<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    <title>{{ __('nav.administration') }} - {{ $branding['club_name'] ?? 'Kbelští sokoli' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
