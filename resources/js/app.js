import './bootstrap';
import collapse from '@alpinejs/collapse';
import AOS from 'aos';
import 'aos/dist/aos.css';
import 'spotlight.js';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';
import { Spinner } from 'spin.js';
import 'spin.js/spin.css';
import { computePosition, flip, shift, offset, size, autoUpdate } from '@floating-ui/dom';

// Floating UI for Alpine.js
window.initFloatingUI = (Alpine) => {
    Alpine.directive('floating', (el, { expression, modifiers }, { evaluate }) => {
        const referenceSelector = evaluate(expression);
        const reference = document.querySelector(referenceSelector);
        if (!reference) return;

        let cleanup;

        // Initialize element styles to prevent jumpy layout and ensure size calculation is clean
        Object.assign(el.style, {
            position: modifiers.includes('fixed') ? 'fixed' : 'absolute',
            left: '0px',
            top: '0px',
            visibility: 'hidden', // Hide until first calculation
        });

        const updatePosition = () => {
            if (!reference.isConnected || !el.isConnected) return;

            computePosition(reference, el, {
                placement: modifiers.includes('top') ? 'top' : (modifiers.includes('bottom') ? 'bottom' : 'top-end'),
                strategy: modifiers.includes('fixed') ? 'fixed' : 'absolute',
                middleware: [
                    offset(12),
                    flip({
                        fallbackPlacements: ['bottom', 'top'],
                        boundary: 'clippingAncestors'
                    }),
                    shift({ padding: 10 }),
                    size({
                        apply({ availableHeight, rects }) {
                            Object.assign(el.style, {
                                maxHeight: `${Math.max(100, availableHeight - 20)}px`,
                                width: modifiers.includes('fixed') ? `${Math.max(256, rects.reference.width)}px` : '',
                            });
                        },
                    }),
                ],
            }).then(({ x, y }) => {
                Object.assign(el.style, {
                    left: `${x}px`,
                    top: `${y}px`,
                    visibility: 'visible',
                });
            });
        };

        const start = () => {
            cleanup = autoUpdate(reference, el, updatePosition);
        };

        const stop = () => {
            if (cleanup) {
                cleanup();
                cleanup = undefined;
            }
            el.style.visibility = 'hidden';
        };

        // Initial check and observe display property
        Alpine.effect(() => {
            const isVisible = el.style.display !== 'none';
            if (isVisible) {
                // Small delay to ensure browser has rendered the reference and the target
                setTimeout(start, 10);
            } else {
                stop();
            }
        });

        // Listen for manual reposition requests
        el.addEventListener('reposition', () => {
            if (el.style.display !== 'none') {
                updatePosition();
            }
        });
    });
};

// Alpine.js fallback: On public/member pages without Livewire, ensure Alpine is available.
if (!window.Alpine) {
    import('alpinejs')
        .then(({ default: Alpine }) => {
            window.Alpine = Alpine;
            Alpine.plugin(collapse);
            window.initFloatingUI(Alpine);
            Alpine.start();
            if (import.meta.env.DEV) {
                console.log('[Alpine] Fallback instance started');
            }
        })
        .catch((e) => console.error('[Alpine] Fallback load failed', e));
} else {
    window.initFloatingUI(window.Alpine);
}

// Initialize AOS (Animate On Scroll)
AOS.init({
    duration: 800,
    easing: 'ease-out-cubic',
    once: true,
    offset: 50,
    delay: 50,
});

// NProgress - Sexy top bar loader
NProgress.configure({ showSpinner: false, trickleSpeed: 200 });
document.addEventListener('livewire:navigating', () => NProgress.start());
document.addEventListener('livewire:navigated', () => NProgress.done());

// Button Loading Logic using spin.js
window.initButtonSpinners = () => {
    const opts = {
        lines: 10, length: 4, width: 2, radius: 5,
        color: '#fff', animation: 'spinner-line-fade-quick',
        top: '50%', left: 'auto', right: '1.5rem', position: 'absolute'
    };

    document.querySelectorAll('.fi-btn').forEach(btn => {
        if (btn.dataset.spinnerInit || btn.closest('.ks-auth-page')) return;
        btn.dataset.spinnerInit = "1";

        // Ensure relative positioning for absolute spinner
        if (getComputedStyle(btn).position === 'static') {
            btn.style.position = 'relative';
        }

        const spinner = new Spinner(opts);
        let instance = null;

        const toggleSpinner = () => {
            const isLoading = btn.classList.contains('fi-processing') || btn.hasAttribute('disabled');
            if (isLoading && !instance) {
                // Add padding to make room for spinner if needed
                btn.style.paddingRight = '3.5rem';
                instance = spinner.spin(btn);
            } else if (!isLoading && instance) {
                btn.style.paddingRight = '';
                instance.stop();
                instance = null;
            }
        };

        // Initial check
        toggleSpinner();

        // Observe class changes
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class' || mutation.attributeName === 'disabled') {
                    toggleSpinner();
                }
            });
        });

        observer.observe(btn, { attributes: true });
    });
};

// Email Protection Logic (Redirect to contact form)
window.initEmailProtection = () => {
    document.querySelectorAll('[data-protected-email]').forEach(el => {
        if (el.dataset.emailInit) return;
        el.dataset.emailInit = "1";

        try {
            const encoded = el.dataset.protectedEmail;
            const email = atob(encoded);

            // Redirect to our contact form instead of mailto:
            el.setAttribute('href', `/napiste-nam?to=${encoded}`);

            // Replace placeholder in text if present
            if (el.textContent.includes('[email]')) {
                el.innerHTML = el.innerHTML.replace('[email]', email);
            }
        } catch (e) {
            console.error('Email protection failed', e);
        }
    });
};

// Analytics / Tracking Readiness
window.initAnalytics = () => {
    document.addEventListener('click', (e) => {
        const target = e.target.closest('[data-track-click]');
        if (!target) return;

        const eventName = target.getAttribute('data-track-click') || 'cta_click';
        const eventLabel = target.getAttribute('data-track-label') || target.innerText.trim();
        const eventCategory = target.getAttribute('data-track-category') || 'engagement';

        // Graceful push to dataLayer if exists
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': 'custom_interaction',
            'event_action': eventName,
            'event_label': eventLabel,
            'event_category': eventCategory
        });

        // Debug log in dev environment
        if (import.meta.env.DEV) {
            console.log(`[Analytics] Tracked: ${eventName} | ${eventLabel} | ${eventCategory}`);
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    window.initButtonSpinners();
    window.initEmailProtection();
    window.initAnalytics();
});

document.addEventListener('livewire:navigated', () => {
    window.initButtonSpinners();
    window.initEmailProtection();
    // initAnalytics relies on document listener, so it doesn't need re-init
});
