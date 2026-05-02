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
          currentLoadingMessage: '',
          updateLoadingMessage() {
              const messages = {{ json_encode($loadingMessages) }};
              const msg = messages[Math.floor(Math.random() * messages.length)];
              this.currentLoadingMessage = msg.replace(':title', '{{ $displayTitle }}');
          }
      }"
      x-init="
          updateLoadingMessage();
          $watch('$store.loader.isVisible', value => {
              if (value) updateLoadingMessage();
          });
      "
      @loading-start.window="$store.loader.start()"
      @loading-stop.window="$store.loader.stop(true)"
      class="contents"
>
    <x-loader-basketball x-show="$store.loader.isVisible" x-cloak class="z-[100000]">
        <span x-html="currentLoadingMessage.replace('Sokol', '<span class=\'text-primary font-black uppercase tracking-wider\'>Sokol</span>').replace('Sokoli', '<span class=\'text-primary font-black uppercase tracking-wider\'>Sokoli</span>')"></span>
    </x-loader-basketball>

    @once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('loader', {
                isVisible: false,
                activeRequests: 0,
                safetyTimer: null,
                stopTimer: null,

                start() {
                    this.isVisible = true;
                    if (this.stopTimer) clearTimeout(this.stopTimer);

                    // Safety timeout - po 60s loader vypneme (prevence "zaseknutí")
                    if (this.safetyTimer) clearTimeout(this.safetyTimer);
                    this.safetyTimer = setTimeout(() => this.stop(true), 60000);
                },

                stop(force = false) {
                    if (force) {
                        this.isVisible = false;
                        this.activeRequests = 0;
                        if (this.safetyTimer) clearTimeout(this.safetyTimer);
                        return;
                    }

                    if (this.activeRequests > 0) {
                        return;
                    }

                    if (this.stopTimer) clearTimeout(this.stopTimer);
                    this.stopTimer = setTimeout(() => {
                        if (this.activeRequests === 0) {
                            this.isVisible = false;
                            if (this.safetyTimer) clearTimeout(this.safetyTimer);
                        }
                    }, 400); // Rychlejší odezva
                }
            });
        });

        // Detekce uživatelské interakce
        window.lastUserInteraction = window.lastUserInteraction || 0;
        ['mousedown', 'keydown', 'submit', 'change', 'click', 'touchstart'].forEach(type => {
            window.addEventListener(type, (e) => {
                window.lastUserInteraction = Date.now();
            }, true);
        });

        // Tlumení tichých Livewire chyb
        window.addEventListener('unhandledrejection', event => {
            if (event.reason && typeof event.reason === 'object' && event.reason.status === null) {
                event.preventDefault();
            }
        });

        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ respond, succeed, fail, options }) => {
                const loader = window.Alpine ? Alpine.store('loader') : null;
                if (!loader) return;

                if (options.method === 'POLL' || options.silent || options.background) return;

                // Ignorujeme dlouhotrvající requesty ze SystemConsole, které mají vlastní indikaci
                if (options.method === 'CALL' && options.params && options.params[0] === 'run') {
                    // Pokud jde o jeden ze synchronizačních příkazů, loader nespouštíme vůbec
                    const cmd = options.params[1] ? options.params[1][0] : null;
                    const syncCommands = ['stats:sync-players', 'stats:sync-team-season', 'stats:import', 'app:sync-player-photos', 'stats:sync-match-detail', 'stats:sync-team-page'];
                    if (syncCommands.includes(cmd)) return;
                }

                const isUserAction = (Date.now() - window.lastUserInteraction) < 1000;
                const isNavigation = !!options.navigate;

                // Sledujeme pouze navigaci nebo explicitní uživatelskou akci (uložení, smazání atd.)
                const shouldTrack = isUserAction || isNavigation;

                if (!shouldTrack) return;

                loader.activeRequests++;
                loader.start();

                let finished = false;
                const done = () => {
                    if (finished) return;
                    finished = true;
                    loader.activeRequests = Math.max(0, loader.activeRequests - 1);
                    loader.stop();
                };

                succeed(done);
                fail(done);
            });
        });

        document.addEventListener('livewire:navigated', () => {
            if (window.Alpine) Alpine.store('loader').stop(true);
        });
    </script>
    @endonce
</div>
