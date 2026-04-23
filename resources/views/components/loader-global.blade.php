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
              // Pokud zpráva obsahuje :title, nahradíme ho (pro loading.page)
              this.currentLoadingMessage = msg.replace(':title', '{{ $displayTitle }}');
          }
      }"
      x-init="updateLoadingMessage()"
      @loading-start.window="globalLoading = true; updateLoadingMessage()"
      @loading-stop.window="globalLoading = false"
      class="contents"
>
    <x-loader-basketball x-show="globalLoading" x-cloak class="z-[100000]">
        <span x-html="currentLoadingMessage.replace('Sokol', '<span class=\'text-primary font-black uppercase tracking-wider\'>Sokol</span>').replace('Sokoli', '<span class=\'text-primary font-black uppercase tracking-wider\'>Sokoli</span>')"></span>
    </x-loader-basketball>

    @once
    <script>
        // Detekce uživatelské interakce pro odlišení od automatických Livewire requestů (polling, Echo)
        if (typeof window.lastUserInteraction === 'undefined') {
            window.lastUserInteraction = 0;
            ['mousedown', 'keydown', 'submit', 'change', 'click', 'touchstart'].forEach(type => {
                window.addEventListener(type, (e) => {
                    window.lastUserInteraction = Date.now();

                    // Okamžité spuštění loaderu při výběru souboru (pokrývá i čas pro client-side resize)
                    if (type === 'change' && e.target && e.target.type === 'file' && e.target.files && e.target.files.length > 0) {
                        window.dispatchEvent(new CustomEvent('loading-start'));
                    }
                }, true);
            });

            // Globální zachytávání unhandled promise rejections pro Livewire 3 (zrušené requesty)
            window.addEventListener('unhandledrejection', event => {
                const error = event.reason;
                if (error && typeof error === 'object' && 'status' in error && error.status === null) {
                    event.preventDefault();
                }
            });
        }

        document.addEventListener('livewire:init', () => {
            // Zachytávání Livewire 3 upload eventů pro globální loader
            window.activeUploads = 0;
            window.activeRequests = 0;
            window.lastUploadFinish = 0;

            // Sjednocená funkce pro vypnutí loaderu s kontrolou všech aktivních procesů
            window.checkAndStopLoader = function() {
                if (window.activeUploads > 0 || window.activeRequests > 0) {
                    return;
                }

                // Malá prodleva před vypnutím loaderu
                // 1. Dává prostor pro následný autosave request po uploadu
                // 2. Zajišťuje, že uživatel stihne vnímat 100% progresu v UI
                setTimeout(() => {
                    if (window.activeUploads === 0 && window.activeRequests === 0) {
                        window.dispatchEvent(new CustomEvent('loading-stop'));
                    }
                }, 600);
            };

            window.addEventListener('livewire:upload-start', () => {
                window.activeUploads++;
                window.dispatchEvent(new CustomEvent('loading-start'));
            });

            window.addEventListener('livewire:upload-finish', () => {
                window.activeUploads = Math.max(0, window.activeUploads - 1);
                window.lastUploadFinish = Date.now();
                window.checkAndStopLoader();
            });

            window.addEventListener('livewire:upload-error', () => {
                window.activeUploads = Math.max(0, window.activeUploads - 1);
                window.checkAndStopLoader();
            });

            Livewire.hook('request', ({ respond, succeed, fail, options }) => {
                // 1. Základní Livewire flagy pro tiché požadavky (včetně pollingu)
                if (options.method === 'POLL' || options.silent || options.background) {
                    return;
                }

                // 2. Detekce, zda požadavek vyvolal uživatel, jde o navigaci, nebo následuje po uploadu (autosave)
                const isAfterUpload = (Date.now() - window.lastUploadFinish) < 1000;
                const isUserAction = (Date.now() - window.lastUserInteraction) < 300 || isAfterUpload;
                const isNavigation = !!options.navigate;

                // 3. Pojistka pro specifické background komponenty (např. dropdown notifikací nebo sync bar)
                const componentName = (options.fingerprint && options.fingerprint.name) || options.name || '';
                const isBackgroundComponent = [
                    'member.notification-dropdown',
                    'sync-status-bar',
                    'sync-status-indicator',
                    'App\\Livewire\\SyncStatusBar'
                ].includes(componentName) || (options.updates && options.updates.some(u => [
                    'member.notification-dropdown',
                    'sync-status-bar',
                    'App\\Livewire\\SyncStatusBar'
                ].includes(u.name)));

                // Background komponenty nikdy nespouštějí globální loader (mají vlastní indikaci)
                if (isBackgroundComponent) {
                    return;
                }

                // 4. Pokud nejde o akci uživatele ani navigaci, loader nepouštíme
                if (!isUserAction && !isNavigation) {
                    return;
                }

                // 5. Ignorujeme stránky s vlastním terminálem nebo specifickou indikací (System Console, AI Search)
                // Povolujeme pouze navigaci NA tyto stránky (aby se loader ukázal při kliknutí v menu),
                // ale při akcích PŘÍMO NA STRÁNCE (isNavigation === false) jej vypínáme.
                const isCustomIndicatorPage = window.location.pathname.includes('/system-console')
                    || window.location.pathname.includes('/ai-search');

                if (isCustomIndicatorPage && !isNavigation) {
                    return;
                }

                window.activeRequests++;
                window.dispatchEvent(new CustomEvent('loading-start'));

                let finished = false;
                const stopRequest = () => {
                    if (finished) return;
                    finished = true;
                    window.activeRequests = Math.max(0, window.activeRequests - 1);
                    window.checkAndStopLoader();
                };

                // Livewire 3 hook callbacks
                // Respond voláme jen pro označení, že data dorazila, ale loader vypínáme až v succeed (po DOM update)
                succeed(stopRequest);
                fail((error) => {
                    const isCancelled = error && typeof error === 'object' && error.status === null;

                    if (isCancelled) {
                        // Suppress background abort errors
                    } else {
                        console.error('[Livewire] Global Loader Error Catch:', error);
                    }
                    stopRequest();
                });
            });
        });

        window.addEventListener('beforeunload', () => {
            window.dispatchEvent(new CustomEvent('loading-start'));
        });

        // Pokud navigace skončí dříve, než se stihne vyvolat beforeunload (rychlé SPA navigace)
        document.addEventListener('livewire:navigated', () => {
            window.activeUploads = 0;
            window.activeRequests = 0;
            window.dispatchEvent(new CustomEvent('loading-stop'));
        });
    </script>
    @endonce
</div>
