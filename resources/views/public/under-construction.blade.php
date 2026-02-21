<!DOCTYPE html>
<html lang="cs" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Web v přípravě' }} | {{ $branding['club_name'] ?? 'Kbelští sokoli' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;800;900&family=Oswald:wght@400;500;600;700&family=Patrick+Hand&display=swap&subset=latin-ext" rel="stylesheet">

    <style>{!! $branding_css !!}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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

        /* Jemná textura místo "kropenatosti" */
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
            font-family: 'Patrick Hand', cursive;
        }

        .chalk-text-primary {
            fill: var(--color-primary);
            opacity: 0.8;
            font-family: 'Patrick Hand', cursive;
            filter: drop-shadow(0 0 10px rgba(var(--color-primary-rgb, 255, 0, 0), 0.5));
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

        @keyframes aura-pulse {
            0%, 100% { transform: scale(1); opacity: 0.2; }
            50% { transform: scale(1.6); opacity: 0.9; }
        }
        .animate-aura {
            animation: aura-pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Garantované pozicování štítku i při problémech s buildem */
        .label-pos-custom {
            position: absolute;
            top: -1rem;
            z-index: 20;
            left: 100%;
            transform: translateX(-80px) rotate(12deg);
            white-space: nowrap;
        }

        @media (min-width: 640px) {
            .label-pos-custom {
                transform: translateX(40px) rotate(12deg);
            }
        }

        @media (min-width: 768px) {
            .label-pos-custom {
                top: -2rem;
                transform: translateX(56px) rotate(12deg);
            }
        }

        @media (min-width: 1024px) {
            .label-pos-custom {
                transform: translateX(80px) rotate(12deg);
            }
        }
    </style>
</head>
<body class="min-h-full flex flex-col items-center justify-center overflow-x-hidden overflow-y-auto text-white selection:bg-primary selection:text-white pb-12">

    <!-- Hlavní obsah -->

    <!-- Taktické prvky na pozadí -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none select-none">
        <!-- Velká červená záře v pozadí - navrácena a vycentrována -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[min(100vw,80rem)] h-[min(100vw,80rem)] bg-primary/20 blur-[150px] rounded-full opacity-60 z-0"></div>

        <!-- Vertikální stmívání - upraveno pro lepší viditelnost prvků -->
        <div class="absolute inset-0 bg-gradient-to-b from-brand-navy/80 via-transparent to-brand-navy/80 z-[2]"></div>

        <svg class="w-full h-full opacity-50 z-[1] relative" viewBox="0 0 1000 1000" preserveAspectRatio="xMidYMid slice">
            <!-- Hřiště -->
            <circle cx="500" cy="500" r="150" class="court-line" style="stroke-opacity: 0.08;" />
            <line x1="0" y1="500" x2="1000" y2="500" class="court-line" style="stroke-opacity: 0.08;" />
            <rect x="250" y="0" width="500" height="200" class="court-line" style="stroke-opacity: 0.08;" />
            <rect x="250" y="800" width="500" height="200" class="court-line" style="stroke-opacity: 0.08;" />

            <!-- Taktika (X a O) - Zmenšeno a vycentrováno pro lepší viditelnost na mobilech -->
            <g class="animate-pulse" style="animation-duration: 8s;">
                <!-- Levá horní skupina -->
                <g transform="translate(200, 250) scale(0.6)">
                    <text x="0" y="0" class="chalk-text text-6xl" style="opacity: 0.2;">X</text>
                    <text x="100" y="120" class="chalk-text text-6xl" style="opacity: 0.2;">O</text>
                    <path d="M 30 30 Q 70 80 90 110" class="chalk-line" style="opacity: 0.3;" />
                </g>

                <!-- Pravá dolní skupina -->
                <g transform="translate(750, 650) scale(0.6)">
                    <text x="0" y="0" class="chalk-text text-6xl" style="opacity: 0.2;">X</text>
                    <text x="-100" y="120" class="chalk-text text-6xl" style="opacity: 0.2;">O</text>
                    <path d="M -30 30 Q -70 80 -90 110" class="chalk-line" style="opacity: 0.3;" />
                </g>

                <!-- Šipka k cíli - posunuta blíž ke středu -->
                <g transform="translate(550, 550) scale(0.7)">
                    <path d="M 0 0 Q 60 100 120 150" class="chalk-line" style="stroke: white; opacity: 0.2;" />
                    <text x="130" y="180" class="chalk-text-primary text-4xl" style="opacity: 0.6;">VÍTĚZSTVÍ!</text>
                </g>
            </g>
        </svg>
    </div>

    <!-- Hlavní kontejner -->
    <div class="relative z-10 w-full max-w-7xl px-6 py-8 md:py-12 flex flex-col items-center text-center">

        <!-- Velký nápis / Stav -->
        <div class="mb-4">
            <span class="inline-flex items-center gap-2 px-6 py-2 bg-primary/10 border border-primary/20 backdrop-blur-md text-primary font-black uppercase tracking-[0.6em] text-xs rounded-full mb-4 shadow-[0_0_30px_rgba(var(--color-primary-rgb),0.2)]">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                </span>
                Status: Time-out
            </span>
        </div>

        <div class="relative w-fit mx-auto mb-4 md:mb-6 overflow-visible text-center">
            <h1 class="w-fit mx-auto text-[12vw] sm:text-7xl md:text-8xl lg:text-9xl font-black uppercase italic tracking-tighter leading-[0.75] mb-6 text-transparent bg-clip-text bg-gradient-to-b from-white via-white to-white/20 px-2 sm:px-12 md:px-16 lg:px-20 py-4 overflow-visible whitespace-nowrap">
                TIME<span class="text-primary">-</span>OUT!
            </h1>
            <div class="label-pos-custom">
                <div class="marker-font text-primary text-lg md:text-3xl lg:text-4xl bg-white px-3 py-1 md:px-5 md:py-2 rounded-sm shadow-[10px_10px_0px_0px_rgba(0,0,0,0.3)] border-2 border-brand-navy whitespace-nowrap">
                    LAKUJEME PALUBOVKU!
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto mb-6 md:mb-8 space-y-4">
            <h2 class="text-2xl md:text-3xl lg:text-4xl font-black uppercase tracking-[0.14em] text-white balance leading-[1.1] italic opacity-95">
                {{ $title ?? 'Trenér právě kreslí vítěznou taktiku pro náš nový web.' }}
            </h2>
            <p class="text-base md:text-lg lg:text-xl text-white leading-[1.6] balance tracking-[0.2em] md:tracking-[0.56em] uppercase opacity-75">
                {{ $text ?? 'Vzali jsme si oddechový čas, abychom do nového webu dostali všechny ty smeče a trojky, které si zasloužíte. Dejte nám chvilku na střídačce, brzy se vrátíme do hry v plné sestavě!' }}
            </p>
        </div>

        <!-- Interaktivní / Vizuální prvek -->
        <div class="relative group cursor-pointer mb-6 md:mb-8">
            <!-- Záře za míčem -->
            <div class="absolute inset-0 bg-primary/20 blur-[100px] rounded-full group-hover:bg-primary/40 transition-colors duration-700 animate-pulse"></div>

            <div class="relative w-24 h-24 md:w-36 md:h-36 mx-auto">
                <!-- Pulzující aura kolem míče -->
                <div class="absolute inset-[-5%] bg-primary/40 blur-2xl rounded-full animate-aura pointer-events-none"></div>

                <!-- Rotující část (Míč + Textura + Linky) -->
                <div class="absolute inset-0 animate-spin-slow transition-all duration-700 group-hover:scale-110">
                    <svg class="w-full h-full text-primary filter drop-shadow-[0_20px_50px_rgba(var(--color-primary-rgb),0.3)]" viewBox="0 0 100 100" fill="currentColor">
                        <defs>
                            <!-- Pebble textura pro "kožený" povrch -->
                            <pattern id="ballPebbles" x="0" y="0" width="8" height="8" patternUnits="userSpaceOnUse">
                                <circle cx="2" cy="2" r="1" fill="black" fill-opacity="0.2" />
                                <circle cx="6" cy="6" r="1" fill="black" fill-opacity="0.2" />
                            </pattern>
                        </defs>
                        <!-- Základní koule -->
                        <circle cx="50" cy="50" r="48" />
                        <!-- Textura -->
                        <circle cx="50" cy="50" r="48" fill="url(#ballPebbles)" />
                        <!-- Basketbalové rýhy (Grooves) -->
                        <g fill="none" stroke="black" stroke-opacity="0.3" stroke-width="2.5" stroke-linecap="round">
                            <circle cx="50" cy="50" r="48" stroke-opacity="0.5" />
                            <path d="M50 2v96" />
                            <path d="M2 50h96" />
                            <path d="M18 18c15 15 15 45 0 60" />
                            <path d="M82 18c-15 15-15 45 0 60" />
                        </g>
                    </svg>
                </div>

                <!-- Statická část (Stínování a Odlesk - nerotuje s míčem pro větší realismus) -->
                <div class="absolute inset-0 pointer-events-none transition-all duration-700 group-hover:scale-110">
                    <svg class="w-full h-full" viewBox="0 0 100 100" fill="none">
                        <defs>
                            <!-- 3D Sférické stínování -->
                            <radialGradient id="ballVolume" cx="35%" cy="35%" r="60%">
                                <stop offset="0%" stop-color="white" stop-opacity="0.4" />
                                <stop offset="50%" stop-color="black" stop-opacity="0" />
                                <stop offset="100%" stop-color="black" stop-opacity="0.5" />
                            </radialGradient>
                            <!-- Vrchní lesk -->
                            <linearGradient id="ballShine" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="white" stop-opacity="0.2" />
                                <stop offset="50%" stop-color="white" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <!-- Stínování -->
                        <circle cx="50" cy="50" r="48" fill="url(#ballVolume)" />
                        <!-- Horní odlesk -->
                        <circle cx="50" cy="50" r="48" fill="url(#ballShine)" />
                    </svg>
                </div>
            </div>

            <!-- Stín pod míčem -->
            <div class="mt-6 w-16 md:w-32 h-2 md:h-3 bg-black/40 blur-md rounded-full mx-auto animate-pulse"></div>
        </div>

        <!-- Akce -->
        <div class="flex flex-col md:flex-row items-stretch md:items-center gap-6 md:gap-16 w-full max-w-lg md:max-w-3xl">
            <div class="flex flex-col items-center flex-1">
                <div class="text-xs font-black uppercase tracking-[0.6em] text-slate-400 mb-6">Pro trenéry</div>
                <a href="/admin" class="w-full md:w-auto group/btn relative overflow-hidden bg-primary text-white hover:bg-white hover:text-primary transition-all px-12 py-5 rounded-2xl font-black uppercase tracking-[0.2em] text-sm shadow-[0_20px_40px_-15px_rgba(var(--color-primary-rgb),0.5)] hover:shadow-none active:scale-95">
                    <span class="relative z-10 flex items-center justify-center gap-3">
                        Vstup do šatny
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 -translate-x-full group-hover/btn:translate-x-full transition-transform duration-700"></div>
                </a>
            </div>

            <div class="hidden md:block w-px h-24 bg-gradient-to-b from-transparent via-white/20 to-transparent"></div>

            <div class="flex flex-col items-center flex-1">
                <div class="text-xs font-black uppercase tracking-[0.6em] text-slate-400 mb-6">Sledujte nás</div>
                <div class="flex items-center gap-5 md:gap-8">
                    @if($branding['socials']['facebook'] ?? null)
                        <a href="{{ $branding['socials']['facebook'] }}" target="_blank" class="p-4 bg-white/10 border border-white/20 rounded-2xl text-white hover:text-primary hover:border-primary hover:bg-white transition-all shadow-xl group/social">
                            <span class="sr-only">Facebook</span>
                            <svg class="w-7 h-7 fill-currentColor" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5v-12h3.642L18 8h-4V6.333C14 5.378 14.192 5 15.115 5H18V0h-3.808C10.596 0 9 1.583 9 4.615V8z"/></svg>
                        </a>
                    @endif
                    @if($branding['socials']['instagram'] ?? null)
                        <a href="{{ $branding['socials']['instagram'] }}" target="_blank" class="p-4 bg-white/10 border border-white/20 rounded-2xl text-white hover:text-primary hover:border-primary hover:bg-white transition-all shadow-xl group/social">
                            <span class="sr-only">Instagram</span>
                            <svg class="w-7 h-7 fill-currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Spodní lišta / Siréna -->
    <div class="relative mt-auto py-6 w-full">
        <div class="container mx-auto flex flex-col items-center">
            <div class="flex flex-col items-center gap-3 mb-4">
                <div class="w-1.5 h-1.5 bg-primary rounded-full animate-ping"></div>
                <div class="text-xs font-black uppercase text-center tracking-[0.15em] md:tracking-[0.8em] text-white/30 translate-x-[0.075em] md:translate-x-[0.4em] max-w-[140px] md:max-w-none">Waiting for the buzzer</div>
            </div>
            <div class="text-[10px] text-white/20 uppercase tracking-[0.3em] font-medium max-w-[280px] md:max-w-none mx-auto leading-relaxed">
                © 2026{{ date('Y') > 2026 ? ' - ' . date('Y') : '' }} {{ $branding['club_name'] ?? 'Kbelští sokoli' }} <br class="md:hidden"> • Všechna práva vyhrazena
            </div>
        </div>
    </div>

</body>
</html>
