@php
    $impersonationStarted = session()->pull('impersonation_started');
    $impersonationStopped = session()->pull('impersonation_stopped');

    \Illuminate\Support\Facades\Log::debug('Impersonation notification check', [
        'started' => $impersonationStarted,
        'stopped' => $impersonationStopped,
        'impersonated_by' => session('impersonated_by'),
        'session_id' => session()->getId(),
        'all_session' => session()->all()
    ]);
@endphp

@if($impersonationStarted || $impersonationStopped)
<div x-data="{
        show: true,
        progress: 100,
        init() {
            console.log('Impersonation notification modal initialized');
            let interval = setInterval(() => {
                this.progress -= 0.5;
                if (this.progress <= 0) {
                    this.show = false;
                    clearInterval(interval);
                }
            }, 25);
            // Backup for auto-close
            setTimeout(() => { this.show = false; }, 5500);
        }
     }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[100000] flex items-center justify-center p-4 overflow-hidden"
     x-cloak>

    <!-- Backdrop overlay (Simplified, integrated into main container if needed, but separate is fine) -->
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-md"></div>

    <!-- Modal Content -->
    <div x-show="show"
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 scale-90 translate-y-8"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-90 translate-y-8"
         class="relative w-full max-w-sm bg-white dark:bg-gray-900 rounded-[3rem] shadow-[0_35px_70px_-15px_rgba(0,0,0,0.5)] overflow-hidden border border-white/20 group">

        <!-- Basketball decoration (Animated Background) -->
        <div class="absolute -right-16 -top-16 w-48 h-48 bg-red-600/5 rounded-full flex items-center justify-center rotate-12 group-hover:rotate-[60deg] transition-transform duration-[3000ms]">
            <i class="fa-light fa-basketball text-[12rem] text-red-600/10"></i>
        </div>

        <div class="relative p-10 text-center">
            <!-- Icon Central Hub -->
            <div class="mx-auto w-28 h-28 rounded-[2.5rem] bg-gradient-to-br {{ $impersonationStarted ? 'from-red-500 to-red-700 shadow-red-500/40' : 'from-blue-600 to-blue-800 shadow-blue-500/40' }} flex items-center justify-center text-white mb-8 shadow-2xl relative">
                <!-- The Main Icon -->
                <i class="fa-light {{ $impersonationStarted ? 'fa-user-secret' : 'fa-arrow-rotate-left' }} text-5xl animate-bounce-slow"></i>

                <!-- Pulsing Aura -->
                <div class="absolute -inset-2 rounded-[2.7rem] border-2 {{ $impersonationStarted ? 'border-red-500/30' : 'border-blue-500/30' }} animate-ping opacity-40"></div>

                <!-- Rotating Border -->
                <div class="absolute -inset-4 rounded-[3rem] border border-dashed {{ $impersonationStarted ? 'border-red-500/20' : 'border-blue-500/20' }} animate-spin-slow"></div>
            </div>

            <h3 class="text-[11px] font-black uppercase tracking-[0.4em] {{ $impersonationStarted ? 'text-red-600' : 'text-blue-600' }} mb-3">
                {{ $impersonationStarted ? __('permissions.impersonation_active') : __('permissions.stop_impersonation') }}
            </h3>

            <h2 class="text-2xl font-black text-gray-900 dark:text-white leading-tight mb-6">
                @if($impersonationStarted)
                    {{ __('permissions.logged_in_as', ['name' => $impersonationStarted]) }}
                @else
                    {{ __('permissions.impersonation_return_success') }}
                @endif
            </h2>

            <!-- Status Badge -->
            <div class="inline-flex items-center gap-2.5 px-5 py-2.5 bg-slate-50 dark:bg-white/5 rounded-full border border-slate-100 dark:border-white/10 shadow-sm">
                <i class="fa-light fa-basketball fa-spin-slow text-red-600 text-sm"></i>
                <span class="text-[11px] font-black text-gray-600 dark:text-gray-400 uppercase tracking-widest">
                    {{ __('permissions.impersonation_game_continues') }}
                </span>
            </div>
        </div>

        <!-- Progress bar timer -->
        <div class="absolute bottom-0 left-0 h-2.5 bg-gradient-to-r {{ $impersonationStarted ? 'from-red-600 via-red-500 to-orange-400' : 'from-blue-600 via-blue-500 to-cyan-400' }} transition-all duration-100 ease-linear shadow-[0_-2px_10px_rgba(0,0,0,0.1)]" :style="'width: ' + progress + '%'"></div>
    </div>
</div>
@endif

<style>
    [x-cloak] { display: none !important; }

    @keyframes bounce-slow {
        0%, 100% { transform: translateY(-10%); animation-timing-function: cubic-bezier(0.8, 0, 1, 1); }
        50% { transform: translateY(0); animation-timing-function: cubic-bezier(0, 0, 0.2, 1); }
    }
    .animate-bounce-slow {
        animation: bounce-slow 1.5s infinite;
    }

    @keyframes spin-slow {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .animate-spin-slow {
        animation: spin-slow 15s linear infinite;
    }
    .fa-spin-slow {
        animation: spin-slow 6s linear infinite;
    }
</style>
