/**
 * KBELŠTÍ SOKOLI - Advanced Auth Validation & Password Strength
 */

const getLocale = () => document.documentElement.lang || 'cs';

const translations = {
    cs: {
        required: "Tohle pole musíte vyplnit, bez toho to nepůjde.",
        email: "Tahle adresa nevypadá jako správný e-mail.",
        capslock: "Caps Lock je zapnutý",
        strength: {
            title: "Heslo není dostatečně silné.",
            length: "Minimálně 8 znaků",
            upper: "Alespoň jedno velké písmeno",
            lower: "Alespoň jedno malé písmeno",
            number: "Alespoň jedno číslo"
        },
        mismatch: "Hesla se neshodují, zkuste to znovu.",
        current_password_required: "Při změně hesla musíte zadat i to stávající.",
        match: {
            empty: "Čekám na přihrávku...",
            partial: "Driblink v pořádku...",
            mismatch: "Ztráta míče! Tohle nesedí.",
            full: "SMEČ! Hesla jsou v týmu."
        }
    },
    en: {
        required: "This field is required, we can't go further without it.",
        email: "This doesn't look like a valid email address.",
        capslock: "Caps Lock is ON",
        strength: {
            title: "Password is not strong enough.",
            length: "At least 8 characters",
            upper: "At least one uppercase letter",
            lower: "At least one lowercase letter",
            number: "At least one number"
        },
        mismatch: "Passwords do not match, please try again.",
        current_password_required: "You must enter your current password when changing it.",
        match: {
            empty: "Waiting for the pass...",
            partial: "Dribbling fine...",
            mismatch: "Turnover! Doesn't match.",
            full: "DUNK! Passwords match."
        }
    }
};

const t = (key) => {
    const locale = getLocale();
    const keys = key.split('.');
    let obj = translations[locale] || translations.cs;
    for (const k of keys) {
        if (!obj[k]) return key;
        obj = obj[k];
    }
    return obj;
};

const disableNativeValidation = () => {
    document.querySelectorAll("form").forEach(form => {
        if (!form.hasAttribute("novalidate")) {
            form.setAttribute("novalidate", "novalidate");
        }
    });
};

const checkStrength = (val) => {
    return {
        length: val.length >= 8,
        upper: /[A-Z]/.test(val),
        lower: /[a-z]/.test(val),
        number: /[0-9]/.test(val)
    };
};

const isConfirmationField = (input) => {
    const ident = (input.name || input.id || input.getAttribute('wire:model') || '').toLowerCase();
    return ident.includes('confirmation');
};

const isValid = (input) => {
    const isPassword = input.type === 'password' || input.classList.contains('fi-revealable');
    const rawVal = input.value;
    const val = isPassword ? rawVal : rawVal.trim();

    if (!val) {
        return !input.hasAttribute("required");
    }

    if (input.type === 'email') return !!val.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/);

    if (isPassword) {
        if (isConfirmationField(input)) {
            const form = input.closest('form');
            const passwordInput = form ? form.querySelector('input[type="password"]:not([id*="Confirmation"]):not([name*="Confirmation"]), input.fi-revealable:not([id*="Confirmation"]):not([name*="Confirmation"])') : null;
            return passwordInput && val === passwordInput.value;
        }

        // Password strength for new passwords
        if (isNewPasswordField(input)) {
            const s = checkStrength(val);
            return s.length && s.upper && s.lower && s.number;
        }
    }

    return true;
};

const isNewPasswordField = (input) => {
    if (isConfirmationField(input)) return false;
    const ident = (input.name || input.id || '').toLowerCase();
    if (ident.includes('current')) return false;

    // Reset password page
    if (window.location.pathname.includes('reset')) {
        return ident === 'password';
    }

    // Profile page or register
    if (ident === 'new_password' || ident === 'password') return true;

    return false;
};

const showClientError = (input, message) => {
    const field = input.closest(".fi-fo-field") || input.closest(".fi-fo-field-wrp");
    if (!field) return;

    const container = field.querySelector(".fi-fo-field-content-col") || field;
    let errorDiv = container.querySelector(".fi-error-message");

    if (!message) {
        if (errorDiv) errorDiv.remove();
        field.classList.remove('ks-invalid');
        return;
    }

    if (!errorDiv) {
        errorDiv = document.createElement("div");
        errorDiv.className = "fi-error-message";
        const span = document.createElement("span");
        errorDiv.appendChild(span);
        container.appendChild(errorDiv);
    }

    errorDiv.querySelector("span").textContent = message;
    field.classList.add('ks-invalid');
    field.classList.remove('ks-valid');
};

const renderPasswordStrength = (field) => {
    if (field.querySelector('.ks-password-strength')) return;

    const container = field.querySelector(".fi-fo-field-content-col") || field;
    const strengthDiv = document.createElement('div');
    strengthDiv.className = 'ks-password-strength animate-fade-in';

    const rules = ['length', 'upper', 'lower', 'number'];
    rules.forEach(rule => {
        const ruleDiv = document.createElement('div');
        ruleDiv.className = `strength-rule rule-${rule}`;
        ruleDiv.innerHTML = `<i class="fa-light fa-circle"></i><span>${t('strength.' + rule)}</span>`;
        strengthDiv.appendChild(ruleDiv);
    });

    container.appendChild(strengthDiv);
};

const updatePasswordStrength = (input) => {
    if (isConfirmationField(input)) return;

    const field = input.closest('.fi-fo-field') || input.closest('.fi-fo-field-wrp');
    if (!field) return;

    const isNewPassword = isNewPasswordField(input);
    if (!isNewPassword) return;

    const val = input.value;

    // Požadavek: zobrazovat až při psaní
    if (!val) {
        const existing = field.querySelector('.ks-password-strength');
        if (existing) existing.remove();
        return;
    }

    renderPasswordStrength(field);
    const s = checkStrength(val);

    Object.keys(s).forEach(rule => {
        const el = field.querySelector(`.rule-${rule}`);
        if (!el) return;
        if (s[rule]) {
            el.classList.add('is-valid');
            el.querySelector('i').className = 'fa-light fa-circle-check';
        } else {
            el.classList.remove('is-valid');
            el.querySelector('i').className = 'fa-light fa-circle';
        }
    });
};

const renderMatchProgress = (field) => {
    let progressDiv = field.querySelector('.ks-match-progress');
    if (progressDiv) return progressDiv;

    const container = field.querySelector(".fi-fo-field-content-col") || field;
    progressDiv = document.createElement('div');
    progressDiv.className = 'ks-match-progress animate-fade-in';
    progressDiv.innerHTML = `
        <div class="match-text"></div>
        <div class="match-bar-container">
            <div class="match-bar"></div>
        </div>
    `;
    container.appendChild(progressDiv);
    return progressDiv;
};

const updateMatchProgress = (input) => {
    if (!isConfirmationField(input)) return;

    const field = input.closest('.fi-fo-field') || input.closest('.fi-fo-field-wrp');
    if (!field) return;

    const val = input.value;

    // Požadavek: progressbar až se začne psát
    if (!val) {
        const existing = field.querySelector('.ks-match-progress');
        if (existing) existing.remove();
        return;
    }

    const progressEl = renderMatchProgress(field);
    const bar = progressEl.querySelector('.match-bar');
    const text = progressEl.querySelector('.match-text');

    const form = input.closest('form');
    const passwordInput = form ? form.querySelector('input[type="password"]:not([id*="Confirmation"]):not([name*="Confirmation"]), input.fi-revealable:not([id*="Confirmation"]):not([name*="Confirmation"])') : null;
    const mainVal = passwordInput ? passwordInput.value : '';

    if (val === mainVal) {
        progressEl.className = 'ks-match-progress animate-fade-in state-full';
        bar.style.width = '100%';
        text.textContent = t('match.full');
    } else if (mainVal.startsWith(val)) {
        progressEl.className = 'ks-match-progress animate-fade-in state-partial';
        const pct = mainVal.length > 0 ? Math.round((val.length / mainVal.length) * 100) : 0;
        bar.style.width = `${pct}%`;
        text.textContent = t('match.partial');
    } else {
        progressEl.className = 'ks-match-progress animate-fade-in state-mismatch';
        bar.style.width = '100%';
        text.textContent = t('match.mismatch');
    }
};

const validateInput = (input, isSubmit = false) => {
    if (!input || input.type === 'hidden' || input.type === 'submit' || input.disabled) return true;

    // Skip hidden inputs (e.g. hidden via x-show)
    if (input.offsetParent === null) return true;

    const field = input.closest('.fi-fo-field') || input.closest('.fi-fo-field-wrp');
    if (!field) return true;

    const ident = (input.name || input.id || '').toLowerCase();
    const isPassword = input.type === 'password' || input.classList.contains('fi-revealable');
    const rawVal = input.value;
    const val = isPassword ? rawVal : rawVal.trim();

    let isInputValid = isValid(input);

    if (isPassword) {
        field.classList.add('ks-password-field');
        updatePasswordStrength(input);

        // Realtime character-by-character confirmation check
        if (isConfirmationField(input)) {
            updateMatchProgress(input);
            const form = input.closest('form');
            const passwordInput = form ? form.querySelector('input[type="password"]:not([id*="Confirmation"]):not([name*="Confirmation"]), input.fi-revealable:not([id*="Confirmation"]):not([name*="Confirmation"])') : null;

            if (passwordInput && rawVal) {
                const mainVal = passwordInput.value;
                if (rawVal === mainVal) {
                    field.classList.add('ks-valid');
                    field.classList.remove('ks-partial', 'ks-mismatch');
                } else if (mainVal.startsWith(rawVal)) {
                    field.classList.add('ks-partial');
                    field.classList.remove('ks-valid', 'ks-mismatch');
                } else {
                    field.classList.add('ks-mismatch');
                    field.classList.remove('ks-valid', 'ks-partial');
                }
            } else {
                field.classList.remove('ks-valid', 'ks-partial', 'ks-mismatch');
            }
        }
    }

    // Additional check for current_password dependency
    if (ident === 'current_password' && !val) {
        const form = input.closest('form');
        const newPass = form ? form.querySelector('input[name="new_password"]') : null;
        if (newPass && newPass.value) {
            isInputValid = false;
        }
    }

    // Success logic
    if (isInputValid) {
        field.classList.add('ks-valid');
        field.classList.remove('ks-invalid');
        showClientError(input, null);
    } else {
        field.classList.remove('ks-valid');
    }

    // Error logic (always show on submit, or if already marked invalid)
    if (isSubmit || field.classList.contains('ks-invalid')) {
        let message = "";
        if (input.hasAttribute("required") && !val) {
            message = t('required');
        } else if (input.type === "email" && val && !val.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            message = t('email');
        } else if (isPassword && !val && input.hasAttribute("required")) {
            message = t('required');
        } else if (isPassword && val && !isValid(input)) {
            if (isConfirmationField(input)) {
                message = t('mismatch');
            } else if (isNewPasswordField(input)) {
                message = t('strength.title');
            }
        } else if (ident === 'current_password' && !val) {
            const form = input.closest('form');
            const newPass = form ? form.querySelector('input[name="new_password"]') : null;
            if (newPass && newPass.value) {
                message = t('current_password_required');
            }
        }

        if (message) {
            showClientError(input, message);
            return false;
        }
    }

    return isInputValid;
};

const init = () => {
    disableNativeValidation();
    document.querySelectorAll('input, select, textarea').forEach(input => {
        if (input.value) validateInput(input);
    });
};

document.addEventListener('input', (e) => {
    if (e.target.matches('input, select, textarea')) {
        validateInput(e.target);

        // If main password changed, re-validate confirmation and current_password
        const isMainPassword = (e.target.type === 'password' || e.target.classList.contains('fi-revealable')) && !isConfirmationField(e.target);
        if (isMainPassword) {
            const confirmationInput = document.querySelector('input[id*="Confirmation"], input[name*="Confirmation"]');
            if (confirmationInput) validateInput(confirmationInput);

            const ident = (e.target.name || e.target.id || '').toLowerCase();
            if (ident === 'new_password') {
                const currentPass = document.querySelector('input[name="current_password"]');
                if (currentPass) validateInput(currentPass, true);
            }
        }
    }
});

document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form.tagName === 'FORM') {
        let isFormValid = true;
        form.querySelectorAll('input, select, textarea').forEach(input => {
            if (!validateInput(input, true)) {
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            e.preventDefault();
            e.stopPropagation();

            const firstErrorField = form.querySelector('.ks-invalid');
            if (firstErrorField) {
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const input = firstErrorField.querySelector('input, select, textarea');
                if (input) input.focus({ preventScroll: true });
            }
        }
    }
}, true);

// Odstranění příliš agresivního observeru, který způsoboval nekonečné smyčky
// Livewire hooky a navigace jsou dostatečné.
/*
const observer = new MutationObserver((mutations) => {
    mutations.forEach(mutation => {
        if (mutation.addedNodes.length) init();
    });
});
observer.observe(document.body, { childList: true, subtree: true });
*/

// Spuštění inicializace
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
document.addEventListener("livewire:navigated", init);

if (window.Livewire) {
    Livewire.hook("request.processed", init);
}
