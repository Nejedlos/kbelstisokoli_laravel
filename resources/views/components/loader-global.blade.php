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
                window.addEventListener(type, () => {
                    window.lastUserInteraction = Date.now();
                }, true);
            });
        }

        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ respond, succeed, fail, options }) => {
                // 1. Základní Livewire flagy pro tiché požadavky
                if (options.method === 'POLL' || options.silent || options.background) {
                    return;
                }

                // 2. Detekce, zda požadavek vyvolal uživatel nebo jde o navigaci
                const isUserAction = (Date.now() - window.lastUserInteraction) < 300; // Mírně zvýšený limit pro jistotu
                const isNavigation = !!options.navigate;

                // 3. Pokud nejde o akci uživatele ani navigaci, loader nepouštíme
                if (!isUserAction && !isNavigation) {
                    return;
                }

                // 4. Pojistka pro specifické background komponenty (např. dropdown notifikací)
                const isNotificationComponent = (options.fingerprint && options.fingerprint.name === 'member.notification-dropdown') ||
                                                (options.name === 'member.notification-dropdown') ||
                                                (options.updates && options.updates.some(u => u.name === 'member.notification-dropdown'));

                if (isNotificationComponent && !isUserAction) {
                    return;
                }

                window.dispatchEvent(new CustomEvent('loading-start'));

                const stopLoading = () => window.dispatchEvent(new CustomEvent('loading-stop'));

                // Livewire 3 hook callbacks
                respond(stopLoading);
                succeed(stopLoading);
                fail((error) => {
                    if (error && typeof error === 'object' && 'status' in error && error.status === null && ('body' in error && error.body === null || 'json' in error)) {
                        // Suppress background abort errors
                    } else {
                        console.error('[Livewire] Global Loader Error Catch:', error);
                    }
                    stopLoading();
                });
            });
        });

        window.addEventListener('beforeunload', () => {
            window.dispatchEvent(new CustomEvent('loading-start'));
        });

        // Pokud navigace skončí dříve, než se stihne vyvolat beforeunload (rychlé SPA navigace)
        document.addEventListener('livewire:navigated', () => {
            window.dispatchEvent(new CustomEvent('loading-stop'));
        });
    </script>
    @endonce
</div>
