@if(\App\Support\ScreenshotMode::isActive())
    <script id="screenshot-mode-scripts">
        window.__SCREENSHOT_READY__ = false;
        document.documentElement.setAttribute('data-screenshot-active', '1');
        document.documentElement.setAttribute('data-screenshot-ready', '0');

        (function() {
            const config = {
                delay: {{ config('screenshot.stability.delay_ms', 500) }},
                waitForFonts: {{ config('screenshot.stability.wait_for_fonts', true) ? 'true' : 'false' }}
            };

            async function signalReady() {
                // 1. Čekání na fonty
                if (config.waitForFonts && document.fonts) {
                    try {
                        await document.fonts.ready;
                    } catch (e) {
                        console.warn('[Screenshot] Fonts error', e);
                    }
                }

                // 2. Čekání na Livewire (pokud je přítomen)
                if (window.Livewire) {
                    await new Promise(resolve => {
                        if (window.Livewire.initialized) {
                            resolve();
                        } else {
                            document.addEventListener('livewire:initialized', resolve, { once: true });
                        }
                    });
                }

                // 3. Čekání na Alpine (pokud je přítomen)
                if (window.Alpine) {
                    // Alpine obvykle běží hned po DOMContentLoaded nebo livewire init
                }

                // 4. Stabilizační delay
                setTimeout(() => {
                    window.__SCREENSHOT_READY__ = true;
                    document.documentElement.setAttribute('data-screenshot-ready', '1');
                    console.log('[Screenshot] Page is READY');
                }, config.delay);
            }

            if (document.readyState === 'complete') {
                signalReady();
            } else {
                window.addEventListener('load', signalReady);
            }
        })();
    </script>
@endif
