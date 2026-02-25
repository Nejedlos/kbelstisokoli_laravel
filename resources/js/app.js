import './bootstrap';
import Alpine from 'alpinejs';
import AOS from 'aos';
import 'aos/dist/aos.css';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';
import { Spinner } from 'spin.js';
import 'spin.js/spin.css';

window.Alpine = Alpine;
Alpine.start();

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
        if (btn.dataset.spinnerInit) return;
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

// Email Protection Logic
window.initEmailProtection = () => {
    document.querySelectorAll('[data-protected-email]').forEach(el => {
        if (el.dataset.emailInit) return;
        el.dataset.emailInit = "1";

        try {
            const encoded = el.dataset.protectedEmail;
            const email = atob(encoded);

            el.setAttribute('href', `mailto:${email}`);

            // Replace placeholder in text if present
            if (el.textContent.includes('[email]')) {
                el.innerHTML = el.innerHTML.replace('[email]', email);
            }
        } catch (e) {
            console.error('Email protection failed', e);
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    window.initButtonSpinners();
    window.initEmailProtection();
});

document.addEventListener('livewire:navigated', () => {
    window.initButtonSpinners();
    window.initEmailProtection();
});
