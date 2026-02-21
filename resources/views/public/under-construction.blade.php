<!DOCTYPE html>
<html lang="cs" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Web v přípravě' }} | {{ $branding['club_name'] ?? 'Kbelští sokoli' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;700&family=Oswald:wght@700&family=Permanent+Marker&display=swap" rel="stylesheet">

    <style>{!! $branding_css !!}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background-color: var(--color-brand-navy);
            background-image:
                radial-gradient(circle at 2px 2px, rgba(255,255,255,0.05) 1px, transparent 0);
            background-size: 40px 40px;
        }

        .marker-font {
            font-family: 'Permanent Marker', cursive;
        }

        .court-line {
            stroke: rgba(255, 255, 255, 0.05);
            stroke-width: 1.5;
            fill: none;
        }

        .chalk-line {
            stroke: var(--color-primary);
            stroke-width: 4;
            stroke-linecap: round;
            stroke-dasharray: 2, 15;
            filter: blur(0.5px);
            opacity: 0.4;
        }

        .chalk-text {
            fill: white;
            opacity: 0.3;
            font-family: 'Permanent Marker', cursive;
        }

        .chalk-text-primary {
            fill: var(--color-primary);
            opacity: 0.8;
            font-family: 'Permanent Marker', cursive;
            filter: drop-shadow(0 0 10px rgba(var(--color-primary-rgb), 0.5));
        }

        @keyframes bounce-slow {
            0%, 100% { transform: translateY(-5%); }
            50% { transform: translateY(0); }
        }
        .animate-bounce-slow {
            animation: bounce-slow 3s infinite ease-in-out;
        }

        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .animate-spin-slow {
            animation: spin-slow 12s infinite linear;
        }
    </style>
</head>
<body class="min-h-full flex flex-col items-center justify-center overflow-x-hidden overflow-y-auto text-white selection:bg-primary selection:text-white pb-12">

    <!-- Grainy overlay pro texturu -->
    <div class="fixed inset-0 z-[1] pointer-events-none opacity-[0.03] contrast-150 brightness-100" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E')"></div>

    <!-- Taktické prvky na pozadí -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none select-none">
        <svg class="w-full h-full" viewBox="0 0 1000 1000" preserveAspectRatio="xMidYMid slice">
            <!-- Hřiště -->
            <circle cx="500" cy="500" r="150" class="court-line" />
            <line x1="0" y1="500" x2="1000" y2="500" class="court-line" />
            <rect x="250" y="0" width="500" height="200" class="court-line" />
            <rect x="250" y="800" width="500" height="200" class="court-line" />

            <!-- Taktika (X a O) -->
            <g class="animate-pulse" style="animation-duration: 4s;">
                <text x="150" y="200" class="chalk-text text-6xl">X</text>
                <text x="250" y="350" class="chalk-text text-6xl">O</text>
                <text x="800" y="700" class="chalk-text text-6xl">X</text>
                <text x="700" y="800" class="chalk-text text-6xl">O</text>

                <path d="M 180 220 Q 220 300 240 330" class="chalk-line" />
                <path d="M 780 720 Q 720 750 710 780" class="chalk-line" />

                <!-- Šipka k cíli -->
                <path d="M 500 600 Q 550 700 600 750" class="chalk-line" style="stroke: white; opacity: 0.2;" />
                <text x="620" y="780" class="chalk-text-primary text-4xl">VÍTĚZSTVÍ!</text>
            </g>
        </svg>
    </div>

    <!-- Hlavní kontejner -->
    <div class="relative z-10 w-full max-w-4xl px-6 py-12 md:py-20 flex flex-col items-center text-center">

        <!-- Velký nápis / Stav -->
        <div class="mb-4">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/5 border border-white/10 backdrop-blur-md text-white font-black uppercase tracking-[0.3em] text-[10px] md:text-xs rounded-full mb-8 shadow-2xl">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                </span>
                Status: Time-out
            </span>
        </div>

        <div class="relative mb-10 md:mb-16">
            <h1 class="text-6xl sm:text-7xl md:text-9xl font-black uppercase italic tracking-tighter leading-[0.8] mb-4 text-transparent bg-clip-text bg-gradient-to-b from-white via-white to-white/20">
                TIME<span class="text-primary">-</span>OUT!
            </h1>
            <div class="absolute -top-4 -right-4 md:-top-8 md:-right-12 animate-bounce-slow">
                <div class="marker-font text-primary text-lg md:text-3xl lg:text-4xl rotate-12 bg-white px-3 py-1 md:px-5 md:py-2 rounded-sm shadow-[10px_10px_0px_0px_rgba(0,0,0,0.3)] border-2 border-brand-navy">
                    Lakujeme palubovku!
                </div>
            </div>
        </div>

        <h2 class="text-xl md:text-4xl font-bold uppercase tracking-tight text-white mb-6 md:mb-8 max-w-2xl balance">
            {{ $title ?? 'Trenér právě kreslí vítěznou taktiku pro náš nový web.' }}
        </h2>

        <p class="text-base md:text-2xl text-slate-300/80 font-medium leading-relaxed mb-10 md:mb-16 max-w-3xl">
            {{ $text ?? 'Vzali jsme si oddechový čas, abychom do nového webu dostali všechny ty smeče a trojky, které si zasloužíte. Dejte nám chvilku na střídačce, brzy se vrátíme do hry v plné sestavě!' }}
        </p>

        <!-- Interaktivní / Vizuální prvek -->
        <div class="relative group cursor-pointer mb-12 md:mb-20">
            <div class="absolute inset-0 bg-primary/20 blur-[100px] rounded-full group-hover:bg-primary/40 transition-colors duration-700 animate-pulse"></div>
            <div class="relative animate-spin-slow">
                <svg class="w-24 h-24 md:w-56 md:h-56 text-primary transition-all duration-700 group-hover:scale-110 group-hover:rotate-[360deg] filter drop-shadow-[0_20px_50px_rgba(var(--color-primary-rgb),0.3)]" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="12" r="10" />
                    <!-- Odlesk na míči -->
                    <path d="M7 7c2-2 5-2 7 0" fill="none" stroke="white" stroke-width="0.5" stroke-linecap="round" stroke-opacity="0.3" />
                    <!-- Basketbalové linky -->
                    <g fill="none" stroke="black" stroke-opacity="0.2" stroke-width="0.8">
                        <path d="M12 2a14.5 14.5 0 0 0 0 20" />
                        <path d="M2 12h20" />
                        <path d="M4.93 4.93a10 10 0 0 1 14.14 0" />
                        <path d="M4.93 19.07a10 10 0 0 0 14.14 0" />
                    </g>
                </svg>
            </div>
            <!-- Stín pod míčem -->
            <div class="mt-6 w-16 md:w-32 h-2 md:h-3 bg-black/40 blur-md rounded-full mx-auto animate-pulse"></div>
        </div>

        <!-- Akce -->
        <div class="flex flex-col md:flex-row items-stretch md:items-center gap-6 md:gap-12 w-full max-w-lg md:max-w-none">
            <div class="flex flex-col items-center flex-1">
                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-4">Pro trenéry</div>
                <a href="/admin" class="w-full md:w-auto btn bg-white/5 border-white/10 text-white hover:bg-white hover:text-brand-navy backdrop-blur-sm transition-all px-10 py-4 rounded-xl font-bold uppercase tracking-widest text-sm border-2">
                    Vstup do šatny
                </a>
            </div>

            <div class="hidden md:block w-px h-16 bg-gradient-to-b from-transparent via-white/10 to-transparent"></div>

            <div class="flex flex-col items-center flex-1">
                <div class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-4">Sledujte nás</div>
                <div class="flex items-center gap-4 md:gap-8">
                    @if($branding['socials']['facebook'] ?? null)
                        <a href="{{ $branding['socials']['facebook'] }}" target="_blank" class="p-3 bg-white/5 border border-white/10 rounded-full text-white/60 hover:text-primary hover:border-primary/50 hover:bg-primary/5 transition-all">
                            <span class="sr-only">Facebook</span>
                            <svg class="w-6 h-6 fill-currentColor" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5v-12h3.642L18 8h-4V6.333C14 5.378 14.192 5 15.115 5H18V0h-3.808C10.596 0 9 1.583 9 4.615V8z"/></svg>
                        </a>
                    @endif
                    @if($branding['socials']['instagram'] ?? null)
                        <a href="{{ $branding['socials']['instagram'] }}" target="_blank" class="p-3 bg-white/5 border border-white/10 rounded-full text-white/60 hover:text-primary hover:border-primary/50 hover:bg-primary/5 transition-all">
                            <span class="sr-only">Instagram</span>
                            <svg class="w-6 h-6 fill-currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Spodní lišta / Siréna -->
    <div class="relative mt-auto py-12 w-full">
        <div class="container mx-auto flex flex-col items-center">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-1.5 h-1.5 bg-primary rounded-full animate-ping"></div>
                <div class="text-[10px] font-black uppercase tracking-[0.8em] text-white/30 translate-x-[0.4em]">Waiting for the buzzer</div>
            </div>
            <div class="text-[10px] text-white/20 uppercase tracking-[0.3em] font-medium">
                © {{ date('Y') }} {{ $branding['club_name'] ?? 'Kbelští sokoli' }} • Všechna práva vyhrazena
            </div>
        </div>
    </div>

</body>
</html>
