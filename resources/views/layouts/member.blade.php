<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Členská sekce - Kbelští sokoli</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header>
        <nav>
            <ul>
                @foreach (data_get(config('navigation.member'), 'header', []) as $item)
                    <li>
                        <a href="{{ route($item['route']) }}">{{ $item['title'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </header>

    <div class="sidebar">
        <nav>
            <ul>
                @foreach (data_get(config('navigation.member'), 'sidebar', []) as $item)
                    <li>
                        <a href="{{ route($item['route']) }}">{{ $item['title'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>

    <main>
        @yield('content')
    </main>
</body>
</html>
