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
            stroke: rgba(255, 255, 255, 0.1);
            stroke-width: 2;
            fill: none;
        }

        .chalk-line {
            stroke: rgba(255, 255, 255, 0.4);
            stroke-width: 3;
            stroke-linecap: round;
            stroke-dasharray: 1, 10;
            filter: blur(0.5px);
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
<body class="h-full flex flex-col items-center justify-center overflow-hidden text-white selection:bg-primary selection:text-white">

    <!-- Taktické prvky na pozadí -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none select-none">
        <svg class="w-full h-full" viewBox="0 0 1000 1000" preserveAspectRatio="xMidYMid slice">
            <!-- Hřiště -->
            <circle cx="500" cy="500" r="150" class="court-line" />
            <line x1="0" y1="500" x2="1000" y2="500" class="court-line" />
            <rect x="250" y="0" width="500" height="200" class="court-line" />
            <rect x="250" y="800" width="500" height="200" class="court-line" />

            <!-- Taktika (X a O) -->
            <g class="opacity-40">
                <text x="150" y="200" class="marker-font text-6xl fill-white">X</text>
                <text x="250" y="350" class="marker-font text-6xl fill-white">O</text>
                <text x="800" y="700" class="marker-font text-6xl fill-white">X</text>
                <text x="700" y="800" class="marker-font text-6xl fill-white">O</text>

                <path d="M 180 220 Q 220 300 240 330" class="chalk-line" />
                <path d="M 780 720 Q 720 750 710 780" class="chalk-line" />

                <!-- Šipka k cíli -->
                <path d="M 500 600 Q 550 700 600 750" class="chalk-line" />
                <text x="620" y="780" class="marker-font text-4xl fill-primary">VÍTĚZSTVÍ!</text>
            </g>
        </svg>
    </div>

    <!-- Hlavní kontejner -->
    <div class="relative z-10 w-full max-w-4xl px-6 py-12 flex flex-col items-center text-center">

        <!-- Velký nápis / Stav -->
        <div class="mb-2">
            <span class="inline-block px-4 py-1 bg-primary text-white font-black uppercase tracking-[0.3em] text-xs rounded-full mb-6">
                Status: Time-out
            </span>
        </div>

        <div class="relative mb-12">
            <h1 class="text-7xl md:text-9xl font-black uppercase italic tracking-tighter leading-none mb-4 text-transparent bg-clip-text bg-gradient-to-b from-white to-white/20">
                TIME<span class="text-primary">-</span>OUT!
            </h1>
            <div class="absolute -top-6 -right-6 md:-right-12 animate-bounce-slow">
                <div class="marker-font text-primary text-2xl md:text-4xl rotate-12 bg-white px-4 py-2 rounded shadow-xl">
                    Lakujeme palubovku!
                </div>
            </div>
        </div>

        <h2 class="text-2xl md:text-4xl font-bold uppercase tracking-tight text-white mb-8 max-w-2xl">
            {{ $title ?? 'Trenér právě kreslí vítěznou taktiku pro náš nový web.' }}
        </h2>

        <p class="text-lg md:text-2xl text-slate-300/80 font-medium leading-relaxed mb-16 max-w-3xl">
            {{ $text ?? 'Vzali jsme si oddechový čas, abychom do nového webu dostali všechny ty smeče a trojky, které si zasloužíte. Dejte nám chvilku na střídačce, brzy se vrátíme do hry v plné sestavě!' }}
        </p>

        <!-- Interaktivní / Vizuální prvek -->
        <div class="relative group cursor-pointer mb-16">
            <div class="absolute inset-0 bg-primary/20 blur-3xl rounded-full group-hover:bg-primary/40 transition-colors duration-500"></div>
            <div class="relative animate-spin-slow">
                <svg class="w-32 h-32 md:w-48 md:h-48 text-primary transition-transform duration-500 group-hover:scale-110" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="12" r="10" />
                    <!-- Basketbalové linky -->
                    <path d="M12 2a14.5 14.5 0 0 0 0 20" fill="none" stroke="rgba(0,0,0,0.3)" stroke-width="1" />
                    <path d="M2 12h20" fill="none" stroke="rgba(0,0,0,0.3)" stroke-width="1" />
                    <path d="M4.93 4.93a10 10 0 0 1 14.14 0" fill="none" stroke="rgba(0,0,0,0.3)" stroke-width="1" />
                    <path d="M4.93 19.07a10 10 0 0 0 14.14 0" fill="none" stroke="rgba(0,0,0,0.3)" stroke-width="1" />
                </svg>
            </div>
            <!-- Stín pod míčem -->
            <div class="mt-4 w-24 h-2 bg-black/40 blur-md rounded-full mx-auto animate-pulse"></div>
        </div>

        <!-- Akce -->
        <div class="flex flex-col md:flex-row items-center gap-8 md:gap-12">
            <div class="flex flex-col items-center">
                <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">Pro trenéry</div>
                <a href="/admin" class="btn btn-outline border-white/20 text-white hover:bg-white hover:text-brand-navy transition-all px-10">
                    Vstup do šatny
                </a>
            </div>

            <div class="w-px h-12 bg-white/10 hidden md:block"></div>

            <div class="flex flex-col items-center">
                <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">Zůstaňte v kontaktu</div>
                <div class="flex items-center gap-6">
                    @if($branding['socials']['facebook'] ?? null)
                        <a href="{{ $branding['socials']['facebook'] }}" target="_blank" class="text-white/60 hover:text-primary transition-colors">
                            <span class="sr-only">Facebook</span>
                            <svg class="w-6 h-6 fill-currentColor" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5v-12h3.642L18 8h-4V6.333C14 5.378 14.192 5 15.115 5H18V0h-3.808C10.596 0 9 1.583 9 4.615V8z"/></svg>
                        </a>
                    @endif
                    @if($branding['socials']['instagram'] ?? null)
                        <a href="{{ $branding['socials']['instagram'] }}" target="_blank" class="text-white/60 hover:text-primary transition-colors">
                            <span class="sr-only">Instagram</span>
                            <svg class="w-6 h-6 fill-currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Spodní lišta / Siréna -->
    <div class="absolute bottom-8 w-full">
        <div class="container flex flex-col items-center">
            <div class="flex items-center gap-4 mb-2">
                <div class="w-2 h-2 bg-primary rounded-full animate-ping"></div>
                <div class="text-[10px] font-black uppercase tracking-[1em] text-white/20 translate-x-[0.5em]">Waiting for the buzzer</div>
            </div>
            <div class="text-[10px] text-white/10 uppercase tracking-widest">
                © {{ date('Y') }} {{ $branding['club_name'] ?? 'Kbelští sokoli' }}
            </div>
        </div>
    </div>

</body>
</html>
