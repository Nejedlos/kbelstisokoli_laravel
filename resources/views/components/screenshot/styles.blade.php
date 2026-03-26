@if(\App\Support\ScreenshotMode::isActive())
    <!-- Screenshot Mode Styles -->
    <style id="screenshot-mode-styles">
        /* Vypnutí animací a přechodů */
        *, *::before, *::after {
            animation: none !important;
            transition: none !important;
            transition-duration: 0s !important;
            scroll-behavior: auto !important;
        }

        /* Skrytí problematických prvků */
        [data-screenshot-hide="true"],
        .cookie-consent,
        .ks-fb-root,
        .ks-fb-overlay,
        .ks-fab-trigger,
        .filament-modal-backdrop,
        .fi-modal-backdrop,
        #intercom-container,
        .chat-widget {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        /* Stabilizace layoutu */
        body {
            overflow: auto !important;
            height: auto !important;
            min-height: 100vh !important;
            background-color: white !important;
        }

        /* Vypnutí blikání kurzoru */
        input, textarea {
            caret-color: transparent !important;
        }

        /* Zajištění viditelnosti lazy load obrázků, které se nestihly načíst */
        img[loading="lazy"] {
            loading: eager !important;
        }

        /* Fixace sticky prvků na static pro headless render, pokud způsobují problémy */
        .sticky, .fixed:not(.screenshot-keep-fixed) {
            /* position: static !important; */
        }
    </style>
@endif
