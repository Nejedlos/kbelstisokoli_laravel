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
            number: "Alespoň jedno číslo",
            special: "Alespoň jeden speciální znak"
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
            number: "At least one number",
            special: "At least one special character"
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
        number: /[0-9]/.test(val),
        special: /[^A-Za-z0-9]/.test(val)
    };
};

const isValid = (input) => {
    const val = input.value.trim();
    if (!val) return false;
    if (input.type === 'email') return !!val.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/);

    // Password strength for new passwords
    if (input.type === 'password' && (document.querySelector('[name*="passwordConfirmation"]') || window.location.pathname.includes('reset'))) {
        const s = checkStrength(val);
        return s.length && s.upper && s.number && s.special;
    }

    return true;
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

    const rules = ['length', 'upper', 'number', 'special'];
    rules.forEach(rule => {
        const ruleDiv = document.createElement('div');
        ruleDiv.className = `strength-rule rule-${rule}`;
        ruleDiv.innerHTML = `<i class="fa-light fa-circle"></i><span>${t('strength.' + rule)}</span>`;
        strengthDiv.appendChild(ruleDiv);
    });

    container.appendChild(strengthDiv);
};

const updatePasswordStrength = (input) => {
    const field = input.closest('.fi-fo-field') || input.closest('.fi-fo-field-wrp');
    if (!field) return;

    const isNewPassword = document.querySelector('[name*="passwordConfirmation"]') || window.location.pathname.includes('reset');
    if (!isNewPassword) return;

    renderPasswordStrength(field);
    const s = checkStrength(input.value);

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

const validateInput = (input, isSubmit = false) => {
    if (!input || input.type === 'hidden' || input.type === 'submit') return;

    const field = input.closest('.fi-fo-field') || input.closest('.fi-fo-field-wrp');
    if (!field) return;

    const val = input.value.trim();
    const isPassword = input.type === 'password' || input.classList.contains('fi-revealable');

    if (isPassword) {
        field.classList.add('ks-password-field');
        updatePasswordStrength(input);
    }

    // Success logic (always on input/change)
    if (isValid(input)) {
        field.classList.add('ks-valid');
        if (!isSubmit) showClientError(input, null);
    } else {
        // Once valid, keep it until explicit error on submit
    }

    // Error logic (only on submit)
    if (isSubmit) {
        let message = "";
        if (input.hasAttribute("required") && !val) {
            message = t('required');
        } else if (input.type === "email" && val && !val.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            message = t('email');
        } else if (isPassword && !val) {
            message = t('required');
        } else if (isPassword && !isValid(input) && (document.querySelector('[name*="passwordConfirmation"]') || window.location.pathname.includes('reset'))) {
            message = t('strength.title');
        }

        if (message) showClientError(input, message);
    }
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
    }
});

document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form.tagName === 'FORM') {
        form.querySelectorAll('input, select, textarea').forEach(input => {
            validateInput(input, true);
        });

        const firstError = form.querySelector('.ks-invalid input');
        if (firstError) firstError.focus();
    }
}, true);

document.addEventListener("DOMContentLoaded", init);
document.addEventListener("livewire:navigated", init);

if (window.Livewire) {
    Livewire.hook("request.processed", init);
}

const observer = new MutationObserver((mutations) => {
    mutations.forEach(mutation => {
        if (mutation.addedNodes.length) init();
    });
});
observer.observe(document.body, { childList: true, subtree: true });

init();
