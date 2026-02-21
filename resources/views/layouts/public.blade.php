<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Kbelští sokoli' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header>
        <!-- Veřejná navigace -->
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <!-- Patička -->
    </footer>
</body>
</html>
