@props([
    'title' => null,
    'subtitle' => null,
    'icon' => 'fa-basketball-hoop',
    'backLabel' => 'Zpět na přihlášení',
    'backUrl' => '/admin/login',
    'showBack' => true,
    // Responsivní maximální šířka formuláře (užší na mobilu, širší na desktopu)
    'maxWidth' => 'max-w-[22rem] sm:max-w-[26rem] md:max-w-[28rem]',
])

@php
    $branding = app(\App\Services\BrandingService::class)->getSettings();
    $colors = $branding['colors'];

    $hexToRgb = function($hex) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return "{$r}, {$g}, {$b}";
    };
@endphp

<div class="ks-auth-page auth-gradient min-h-dvh flex items-start md:items-center justify-center py-10 px-4 md:px-6 lg:px-8 relative overflow-hidden"
     style="
        /* Světlejší sport-tech gradient v brand barvách */
        background-color: #0f172a !important;
        background-image:
            radial-gradient(1200px 700px at 50% -10%, rgba({{ $hexToRgb($colors['red'] ?? '#e11d48') }}, 0.50) 0%, rgba({{ $hexToRgb($colors['red'] ?? '#e11d48') }}, 0.18) 40%, transparent 72%),
            radial-gradient(1400px 900px at 0% 100%, rgba({{ $hexToRgb($colors['navy'] ?? '#0b1f3a') }}, 0.28) 0%, rgba({{ $hexToRgb($colors['navy'] ?? '#0b1f3a') }}, 0.12) 50%, transparent 82%),
            radial-gradient(1200px 900px at 100% 100%, rgba({{ $hexToRgb($colors['blue'] ?? '#2563eb') }}, 0.26) 0%, rgba({{ $hexToRgb($colors['blue'] ?? '#2563eb') }}, 0.10) 55%, transparent 85%),
            radial-gradient(1000px 600px at 50% 10%, rgba(255, 255, 255, 0.12) 0%, transparent 70%) !important;
        background-attachment: fixed !important;
        background-size: cover !important;
    "
>
    <style>
        :root {
            /* Brand tokens */
            --brand-navy: {{ $colors['navy'] ?? '#0b1f3a' }};
            --brand-navy-rgb: {{ $hexToRgb($colors['navy'] ?? '#0b1f3a') }};
            --brand-blue: {{ $colors['blue'] ?? '#2563eb' }};
            --brand-blue-rgb: {{ $hexToRgb($colors['blue'] ?? '#2563eb') }};
            --brand-red: {{ $colors['red'] ?? '#e11d48' }};
            --brand-red-rgb: {{ $hexToRgb($colors['red'] ?? '#e11d48') }};
            --brand-red-hover: {{ $colors['red_hover'] ?? '#be123c' }};
            --brand-white: #ffffff;

            /* UI tokens */
            --ui-text: rgba(255, 255, 255, 0.92);
            --ui-text-muted: rgba(255, 255, 255, 0.65);
            --ui-border: rgba(255, 255, 255, 0.18);
            --ui-surface: rgba(255, 255, 255, 0.80);
            --ui-surface-elevated: rgba(255, 255, 255, 0.90);
            --ui-success: #22c55e;
            --ui-danger: #ef4444;
            --ui-warning: #f59e0b;
        }
    </style>
    {{-- Background Objects like in 2FA layout --}}
    <div class="floating-objects pointer-events-none">
        <div class="floating-ball w-64 h-64 top-[-10%] left-[-5%]"></div>
        <div class="floating-ball w-96 h-96 bottom-[-15%] right-[-10%]"></div>
    </div>

    <div class="w-full {{ $maxWidth }} relative z-10">
        {{-- Language Switcher --}}
        <div class="flex justify-center mb-6 md:mb-8">
            <!-- Project language switcher (frontend style) -->
            <div class="flex items-center gap-1 bg-white/10 backdrop-blur-sm p-1 rounded-full text-[10px] font-black tracking-tighter shadow-sm border border-white/15">
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'cs']) }}"
                   class="px-3 py-2 rounded-full transition-all cursor-pointer {{ app()->getLocale() === 'cs' ? 'bg-white text-slate-900 shadow-sm' : 'text-white/80 hover:text-white hover:bg-white/10' }}"
                   aria-label="Čeština">
                    CZ
                </a>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}"
                   class="px-3 py-2 rounded-full transition-all cursor-pointer {{ app()->getLocale() === 'en' ? 'bg-white text-slate-900 shadow-sm' : 'text-white/80 hover:text-white hover:bg-white/10' }}"
                   aria-label="English">
                    EN
                </a>
            </div>
        </div>

        {{-- Header --}}
        <x-auth-header :title="$title" :subtitle="$subtitle" :icon="$icon" />

        {{-- Form Surface --}}
        <div class="glass-card animate-fade-in-down" style="animation-delay: 0.1s">
            <div class="relative">
                {{ $slot }}
            </div>

            <!-- Links under form -->
            <div class="mt-6 flex items-center justify-between gap-4 text-sm">
                <a href="{{ route('filament.admin.auth.password-reset.request') }}" class="fi-link inline-flex items-center gap-2" aria-label="Zapomněli jste heslo?">
                    <i class="fa-light fa-key"></i>
                    <span>Zapomněli jste heslo?</span>
                </a>
                <span class="text-slate-400/80 text-xs">&nbsp;</span>
            </div>
        </div>

        {{-- Footer / Back Link --}}
        @if($showBack)
            <x-auth-footer :back-label="$backLabel" :back-url="$backUrl" :show-back="true" />
        @else
            <x-auth-footer :show-back="false" />
        @endif
    </div>
</div>
