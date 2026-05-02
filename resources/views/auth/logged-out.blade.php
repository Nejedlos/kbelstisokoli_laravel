<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Byl jste odhlášen') }} | {{ $branding['club_name'] ?? 'Kbelští sokoli' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;800;900&family=Oswald:wght@400;500;600;700&family=Patrick+Hand&display=swap&subset=latin-ext" rel="stylesheet">

    <style>{!! $branding_css ?? '' !!}</style>
    @vite(['resources/css/icons-fix.css', 'resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background-color: var(--color-brand-navy);
            background-image:
                radial-gradient(circle at 50% -20%, rgba(var(--color-primary-rgb, 255, 0, 0), 0.1) 0%, transparent 70%),
                radial-gradient(circle at 0% 100%, rgba(var(--color-brand-blue-rgb, 59, 130, 246), 0.15) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(var(--color-brand-blue-rgb, 59, 130, 246), 0.15) 0%, transparent 50%);
            background-attachment: fixed;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            opacity: 0.02;
            pointer-events: none;
            z-index: 1;
        }

        .marker-font {
            font-family: 'Patrick Hand', cursive;
        }

        @keyframes bounce-slow {
            0%, 100% { transform: translateY(-5%); }
            50% { transform: translateY(0); }
        }
        .animate-bounce-slow {
            animation: bounce-slow 3s infinite ease-in-out;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 antialiased overflow-hidden">

    <div class="relative w-full max-w-2xl z-10">
        <!-- Logo / Branding -->
        <div class="flex justify-center mb-12">
            @if(isset($branding['logo_url']))
                <img src="{{ $branding['logo_url'] }}" alt="Logo" class="h-24 w-auto drop-shadow-2xl animate-bounce-slow">
            @else
                <div class="h-24 w-24 bg-primary rounded-full flex items-center justify-center animate-bounce-slow shadow-lg shadow-primary/20">
                    <i class="fa-light fa-basketball text-5xl text-white"></i>
                </div>
            @endif
        </div>

        <div class="glass-card rounded-3xl p-8 md:p-12 text-center relative overflow-hidden group">
            <!-- Dekorativní prvky -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary/10 rounded-full blur-3xl group-hover:bg-primary/20 transition-colors duration-700"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-brand-blue/10 rounded-full blur-3xl group-hover:bg-brand-blue/20 transition-colors duration-700"></div>

            <div class="relative z-10">
                <h1 class="text-4xl md:text-5xl font-black text-white mb-6 uppercase tracking-tight leading-none">
                    {{ __('Byl jste odhlášen') }}
                </h1>

                <p class="text-xl md:text-2xl text-white/70 font-medium mb-10 leading-relaxed max-w-lg mx-auto">
                    {{ __('Děkujeme za návštěvu. Vaše relace byla bezpečně ukončena. Těšíme se na Vaši další návštěvu na palubovce!') }}
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-brand-navy font-bold rounded-xl transition-all hover:bg-white/90 hover:scale-105 active:scale-95 w-full sm:w-auto">
                        <i class="fa-light fa-house mr-2"></i>
                        {{ __('Zpět na web') }}
                    </a>
                    <a href="{{ url('/login') }}" class="inline-flex items-center justify-center px-8 py-4 bg-primary text-white font-bold rounded-xl transition-all hover:bg-primary/90 hover:scale-105 active:scale-95 w-full sm:w-auto shadow-lg shadow-primary/25">
                        <i class="fa-light fa-right-to-bracket mr-2"></i>
                        {{ __('Přihlásit se znovu') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Chalk text (estetický prvek) -->
        <div class="mt-8 text-center opacity-30 marker-font text-white text-lg">
            #KbelstiSokoli #BasketballFamily
        </div>
    </div>

    <!-- Background Decoration (Court Lines) -->
    <div class="fixed inset-0 pointer-events-none opacity-20">
        <svg class="w-full h-full" viewBox="0 0 1000 1000" preserveAspectRatio="xMidYMid slice">
            <circle cx="500" cy="500" r="150" class="stroke-white/20 fill-none" stroke-width="2" />
            <circle cx="500" cy="500" r="2" class="fill-white/20" />
            <line x1="0" y1="500" x2="1000" y2="500" class="stroke-white/20" stroke-width="2" />
            <rect x="350" y="0" width="300" height="200" class="stroke-white/20 fill-none" stroke-width="2" />
            <rect x="350" y="800" width="300" height="200" class="stroke-white/20 fill-none" stroke-width="2" />
        </svg>
    </div>

</body>
</html>
