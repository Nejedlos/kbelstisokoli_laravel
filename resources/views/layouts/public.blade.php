<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Kbelští sokoli' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(isset($head_code))
        {!! $head_code !!}
    @endif

    @stack('head')
</head>
<body>
    <header>
        <nav>
            <ul>
                @foreach (config('navigation.public', []) as $item)
                    <li>
                        <a href="{{ route($item['route']) }}">{{ $item['title'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <!-- Patička -->
    </footer>

    @if(isset($footer_code))
        {!! $footer_code !!}
    @endif

    @stack('scripts')
</body>
</html>
