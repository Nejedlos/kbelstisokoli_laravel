@props([
    'title' => null,
])

@php
    $loadingKey = match(true) {
        request()->routeIs('member.dashboard') => 'dashboard',
        request()->routeIs('member.attendance.*') => 'attendance',
        request()->routeIs('member.profile.*') => 'profile',
        request()->routeIs('member.economy.*') => 'economy',
        request()->routeIs('member.teams.*') => 'teams',
        request()->routeIs('member.notifications.*') => 'notifications',
        request()->routeIs('member.search') => 'search',
        request()->routeIs('member.ai') => 'ai',
        request()->routeIs('filament.admin.*') => 'admin',
        default => 'page'
    };

    // Helper pro brand text (v Blade partialu nemusí být dostupný, pokud není v ServiceProvideru)
    $brandText = function($text) {
        return str_replace(['Sokol', 'Sokoli'], ['<span class="text-primary font-black uppercase tracking-wider">Sokol</span>', '<span class="text-primary font-black uppercase tracking-wider">Sokoli</span>'], $text);
    };

    $displayTitle = $title ?? ($loadingKey === 'admin' ? 'Administrace' : __('nav.member_section'));
    $loadingMessages = __('member.loading.' . $loadingKey);

    if ($loadingKey === 'page' || $loadingKey === 'admin') {
        $loadingMessages = [__('member.loading.page', ['title' => $displayTitle])];
    } elseif (!is_array($loadingMessages)) {
        $loadingMessages = [$loadingMessages ?: __('member.loading.generic')];
    }
@endphp

<div x-data="{
          globalLoading: false,
          loadingMessages: {{ json_encode($loadingMessages) }},
          currentLoadingMessage: '',
          updateLoadingMessage() {
              const msg = this.loadingMessages[Math.floor(Math.random() * this.loadingMessages.length)];
              this.currentLoadingMessage = msg.replace(':title', '{{ $displayTitle }}');
          }
      }"
      x-init="
          updateLoadingMessage();
          window.addEventListener('loading-start', () => { globalLoading = true; updateLoadingMessage(); });
          window.addEventListener('loading-stop', () => { globalLoading = false; });
      "
      class="contents"
>
    <x-loader-basketball x-show="globalLoading" x-cloak class="z-[100000]">
        <span x-html="currentLoadingMessage.replace('Sokol', '<span class=\'text-primary font-black uppercase tracking-wider\'>Sokol</span>').replace('Sokoli', '<span class=\'text-primary font-black uppercase tracking-wider\'>Sokoli</span>')"></span>
    </x-loader-basketball>

    @once
    <script>
        // Detekce uživatelské interakce
        window.lastUserInteraction = window.lastUserInteraction || 0;
        ['mousedown', 'keydown', 'submit', 'change', 'click', 'touchstart'].forEach(type => {
            window.addEventListener(type, (e) => {
                window.lastUserInteraction = Date.now();
                if (type === 'change' && e.target && e.target.type === 'file' && e.target.files && e.target.files.length > 0) {
                    window.dispatchEvent(new CustomEvent('loading-start'));
                }
            }, true);
        });

        // Tlumení tichých Livewire chyb (zrušené požadavky)
        window.addEventListener('unhandledrejection', event => {
            if (event.reason && typeof event.reason === 'object' && event.reason.status === null) {
                event.preventDefault();
            }
        });

        // Livewire integration
        document.addEventListener('livewire:init', () => {
            window.activeUploads = 0;
            window.activeRequests = 0;
            window.lastUploadFinish = 0;
            window.expectingFinalRequest = false;
            window.loaderSafetyTimeout = null;

            const debugLoading = localStorage.getItem('debug_loading') === 'true';
            const log = (...args) => { if (debugLoading) console.log('[Loader]', ...args); };

            window.checkAndStopLoader = function(force = false) {
                log('Checking stop:', { uploads: window.activeUploads, requests: window.activeRequests, force, expecting: window.expectingFinalRequest });

                if (!force && (window.activeUploads > 0 || window.activeRequests > 0 || window.expectingFinalRequest)) {
                    return;
                }

                // Malá prodleva pro UI a řetězené akce
                setTimeout(() => {
                    if (force || (window.activeUploads === 0 && window.activeRequests === 0)) {
                        log('Stopping loader now');
                        window.dispatchEvent(new CustomEvent('loading-stop'));
                        window.expectingFinalRequest = false;
                        if (window.loaderSafetyTimeout) clearTimeout(window.loaderSafetyTimeout);
                    }
                }, 500);
            };

            // Event ze serveru po autosavu
            window.addEventListener('autosave-finished', () => {
                log('Autosave finished signal received');
                window.expectingFinalRequest = false;
                window.activeRequests = 0; // Agresivní reset pro jistotu
                window.checkAndStopLoader(true);
            });

            window.addEventListener('livewire:upload-start', () => {
                window.activeUploads++;
                log('Upload started', window.activeUploads);
                window.dispatchEvent(new CustomEvent('loading-start'));

                // Safety timeout - pokud se nic nestane do 2 minut (velké soubory), loader vypneme
                if (window.loaderSafetyTimeout) clearTimeout(window.loaderSafetyTimeout);
                window.loaderSafetyTimeout = setTimeout(() => {
                    log('Safety timeout hit');
                    window.checkAndStopLoader(true);
                }, 120000);
            });

            window.addEventListener('livewire:upload-finish', () => {
                window.activeUploads = Math.max(0, window.activeUploads - 1);
                window.lastUploadFinish = Date.now();
                window.expectingFinalRequest = true; // Budeme čekat na následný autosave request
                log('Upload finished, expecting final request');

                // Pokud do 2 sekund nezačne žádný request, flag zrušíme
                setTimeout(() => {
                    if (window.activeRequests === 0 && window.expectingFinalRequest) {
                        log('No final request started, clearing flag');
                        window.expectingFinalRequest = false;
                        window.checkAndStopLoader();
                    }
                }, 2000);

                window.checkAndStopLoader();
            });

            window.addEventListener('livewire:upload-error', () => {
                window.activeUploads = Math.max(0, window.activeUploads - 1);
                window.checkAndStopLoader(true);
            });

            Livewire.hook('request', ({ respond, succeed, fail, options }) => {
                if (options.method === 'POLL' || options.silent || options.background) return;

                const isUserAction = (Date.now() - window.lastUserInteraction) < 1000;
                const isNavigation = !!options.navigate;
                const shouldShow = isUserAction || isNavigation || window.expectingFinalRequest || window.activeUploads > 0;

                if (!shouldShow) return;

                window.activeRequests++;
                log('Request started', { active: window.activeRequests, expecting: window.expectingFinalRequest });
                window.dispatchEvent(new CustomEvent('loading-start'));

                let finished = false;
                const stopRequest = () => {
                    if (finished) return;
                    finished = true;
                    window.activeRequests = Math.max(0, window.activeRequests - 1);
                    log('Request finished', window.activeRequests);
                    window.checkAndStopLoader();
                };

                succeed(stopRequest);
                fail(() => { stopRequest(); });
            });
        });

        document.addEventListener('livewire:navigated', () => {
            window.activeUploads = 0;
            window.activeRequests = 0;
            window.expectingFinalRequest = false;
            window.dispatchEvent(new CustomEvent('loading-stop'));
        });
    </script>
    @endonce
</div>
