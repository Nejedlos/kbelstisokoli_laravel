@php
    // Předstíráme Livewire objekt pro layout, pokud neexistuje
    $livewire = new class {
        public function getHeading() { return __('Relace vypršela'); }
        public function getSubheading() { return __('Vaše přihlášení již není platné. Prosím, přihlaste se znovu.'); }
        public function getIcon() { return 'fa-clock-rotate-left'; }
        public function getRenderHookScopes(): array { return []; }
        public function getTitle(): string { return __('Relace vypršela'); }
        public function getExtraBodyAttributes(): array { return []; }
    };
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    <div class="ks-auth-page auth-gradient w-full min-h-dvh flex items-center justify-center py-6 px-4 md:px-6 lg:px-8 relative overflow-x-hidden"
         style="
            background-color: #0f172a !important;
            background-image:
                radial-gradient(1200px 700px at 50% -10%, rgba(225, 29, 72, 0.40) 0%, rgba(225, 29, 72, 0.12) 40%, transparent 72%),
                radial-gradient(1400px 900px at 0% 100%, rgba(11, 31, 58, 0.28) 0%, rgba(11, 31, 58, 0.12) 50%, transparent 82%),
                radial-gradient(1200px 900px at 100% 100%, rgba(37, 99, 235, 0.26) 0%, rgba(37, 99, 235, 0.10) 55%, transparent 85%),
                radial-gradient(1000px 600px at 50% 10%, rgba(255, 255, 255, 0.08) 0%, transparent 70%) !important;
            background-attachment: fixed !important;
            background-size: cover !important;
        "
    >
        @vite(['resources/css/filament-auth.css', 'resources/js/filament-auth.js'])

        {{-- Background Elements (Tactical & Atmospheric) --}}
        <div class="absolute inset-0 z-0 pointer-events-none select-none overflow-hidden">
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=\'0 0 256 256\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cfilter id=\'noiseFilter\'%3E%3CfeTurbulence type=\'fractalNoise\' baseFrequency=\'0.65\' numOctaves=\'3\' stitchTiles=\'stitch\'/%3E%3C/filter%3E%3Crect width=\'100%25\' height=\'100%25\' filter=\'url(%23noiseFilter)\'/%3E%3C/svg%3E');"></div>
        </div>

        <div class="ks-auth-container w-full max-w-[22rem] sm:max-w-[28rem] md:max-w-[32rem] relative z-10 py-10">
            <!-- Header -->
            <x-auth-header
                :title="__('419 | Relace vypršela')"
                :subtitle="__('Stránka expirovala z důvodu neaktivity. Prosím, vraťte se na přihlášení.')"
                icon="fa-clock-rotate-left"
            />

            <!-- Content Surface -->
            <div class="glass-card animate-fade-in-down" style="animation-delay: 0.1s">
                <div class="text-center py-6">
                    <p class="text-white/70 mb-8">
                        {{ __('Bezpečnostní token vypršel. To se stává, když necháte stránku příliš dlouho otevřenou bez akce.') }}
                    </p>

                    <div class="flex flex-col gap-3">
                        <a href="{{ route('filament.admin.auth.login') }}" class="ks-auth-btn-primary w-full justify-center">
                            <i class="fa-light fa-right-to-bracket mr-2"></i>
                            {{ __('Přejít na přihlášení') }}
                        </a>

                        <button onclick="window.location.reload()" class="ks-auth-btn-secondary w-full justify-center">
                            <i class="fa-light fa-rotate-right mr-2"></i>
                            {{ __('Zkusit znovu načíst') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <x-auth-footer :back-label="__('Zpět na hlavní stránku')" :back-url="url('/')" :show-back="true" />
        </div>
    </div>
</x-filament-panels::layout.base>
