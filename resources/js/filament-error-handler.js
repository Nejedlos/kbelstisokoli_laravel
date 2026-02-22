const disableNativeValidation = () => {
    document.querySelectorAll("form").forEach(form => {
        if (!form.hasAttribute("novalidate")) {
            form.setAttribute("novalidate", "novalidate");
        }
    });
};

const updateErrorStates = () => {
    document.querySelectorAll(".fi-fo-field").forEach(field => {
        const hasError = field.querySelector(".fi-fo-field-wrp-error-message, .fi-fo-field-error-message, .text-danger-600, [id*='-error'], .fi-error-message, .fi-fo-field-error");
        const inputWrp = field.querySelector(".fi-input-wrp");
        const input = field.querySelector("input, select, textarea");
        const label = field.querySelector(".fi-fo-field-label, .fi-fo-field-label-content");

        if (hasError) {
            field.classList.add("fi-invalid-field");
            if (inputWrp) inputWrp.classList.add("fi-is-invalid");
            if (input) input.classList.add("fi-is-invalid");
            if (label) {
                label.classList.add("fi-is-invalid");
                label.style.setProperty('color', '#E11D48', 'important');
            }
        } else {
            field.classList.remove("fi-invalid-field");
            if (inputWrp) inputWrp.classList.remove("fi-is-invalid");
            if (input) input.classList.remove("fi-is-invalid");
            if (label) {
                label.classList.remove("fi-is-invalid");
                label.style.color = "";
            }
        }
    });
};

const showClientError = (input, message) => {
    const container = input.closest(".fi-fo-field-content-col");
    if (!container) return;

    const existing = container.querySelector(".fi-error-message");
    if (existing) existing.remove();

    if (message) {
        const errorDiv = document.createElement("div");
        errorDiv.className = "fi-error-message";
        const span = document.createElement("span");
        span.textContent = message;
        errorDiv.appendChild(span);
        container.appendChild(errorDiv);
    }
    updateErrorStates();
};

const validateInput = (input) => {
    if (input.type === "checkbox" || input.type === "hidden" || input.type === "submit") return;
    let message = "";
    if (input.hasAttribute("required") && !input.value.trim()) {
        message = "Tohle pole musíte vyplnit, bez toho to nepůjde.";
    } else if (input.type === "email" && input.value && !input.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        message = "Tahle adresa nevypadá jako správný e-mail.";
    }
    showClientError(input, message);
};

// Global handlers
window.handleFormValidationError = () => {
    setTimeout(updateErrorStates, 50);
};

// Event Listeners
document.addEventListener("focusout", (e) => {
    if (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA" || e.target.tagName === "SELECT") {
        validateInput(e.target);
    }
}, true);

document.addEventListener("input", (e) => {
    if (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA" || e.target.tagName === "SELECT") {
        const container = e.target.closest(".fi-fo-field-content-col");
        if (container?.querySelector(".fi-error-message")) {
            validateInput(e.target);
        }
        updateErrorStates();
    }
});

document.addEventListener("submit", (e) => {
    e.target.querySelectorAll("input, select, textarea").forEach(input => validateInput(input));
    updateErrorStates();
}, true);

// Filament / Livewire integration
const initErrorHandler = () => {
    disableNativeValidation();
    updateErrorStates();
};

document.addEventListener("DOMContentLoaded", initErrorHandler);
document.addEventListener("livewire:navigated", initErrorHandler);

window.addEventListener("livewire:initialized", () => {
    Livewire.hook("request.processed", () => {
        initErrorHandler();
    });
});

// Mutation Observer for dynamic fields
const observer = new MutationObserver(() => {
    initErrorHandler();
});
observer.observe(document.body, { childList: true, subtree: true });

// First run
initErrorHandler();
