/*
 Kbelští sokoli – Auth microinteractions
 - Caps Lock indicator for password
 - Loading state on submit button (no layout shift)
 - Gentle shake on first invalid field
 - Livewire v3 friendly re-init on navigations
*/

(function () {
  const Q = (s, r = document) => r.querySelector(s);
  const QA = (s, r = document) => Array.from(r.querySelectorAll(s));

  function attachCapsLockIndicator(root) {
    const passWrp = Q('.fi-fo-field-wrp:has(input[type="password"])', root);
    if (!passWrp) return;
    let indicator = Q('.ks-caps-indicator', passWrp);
    if (!indicator) {
      indicator = document.createElement('div');
      indicator.className = 'ks-caps-indicator';
      indicator.setAttribute('role', 'status');
      indicator.setAttribute('aria-live', 'polite');
      indicator.style.cssText = 'margin-top:0.5rem;font-size:0.75rem;color:#ef4444;display:none;align-items:center;gap:.5rem;';
      indicator.innerHTML = '<i class="fa-light fa-arrow-up-a-z"></i><span>Caps Lock je zapnutý</span>';
      passWrp.appendChild(indicator);
    }
    const pass = Q('input[type="password"]', passWrp);
    if (!pass) return;
    const onKey = (e) => {
      const on = e.getModifierState && e.getModifierState('CapsLock');
      indicator.style.display = on ? 'flex' : 'none';
    };
    pass.removeEventListener('keyup', onKey);
    pass.addEventListener('keyup', onKey, { passive: true });
  }

  function attachSubmitLoading(root) {
    const form = Q('form[wire\\:submit], form[wire\\:submit\\.prevent]', root);
    if (!form) return;
    const submit = Q('button[type="submit"], .fi-btn-color-primary', form);
    if (!submit) return;
    const start = () => submit.classList.add('is-loading');
    const stop = () => submit.classList.remove('is-loading');

    form.addEventListener('submit', start);

    if (window.Livewire && typeof Livewire.hook === 'function') {
      Livewire.hook('request', ({ succeed, fail }) => {
        succeed(() => stop());
        fail(() => {
          stop();
          gentleShakeFirstInvalid(root);
        });
      });
    } else {
      form.addEventListener('ajax:complete', stop);
    }
  }

  function gentleShakeFirstInvalid(root) {
    const firstErr = Q('.fi-fo-field-wrp-error-message, .fi-fo-field-error-message, .fi-error-message', root);
    const card = Q('.glass-card', root) || root;
    if (firstErr) {
      const wrp = firstErr.closest('.fi-fo-field-wrp') || card;
      wrp.style.animation = 'shake .4s cubic-bezier(.36,.07,.19,.97)';
      setTimeout(() => (wrp.style.animation = ''), 450);
      const input = Q('input, textarea, select', wrp);
      input && input.focus({ preventScroll: true });
    } else if (card) {
      card.style.animation = 'shake .35s cubic-bezier(.36,.07,.19,.97)';
      setTimeout(() => (card.style.animation = ''), 380);
    }
  }

  function initAuthUI() {
    const root = Q('.ks-auth-page') || document;
    attachCapsLockIndicator(root);
    attachSubmitLoading(root);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAuthUI, { once: true });
  } else {
    initAuthUI();
  }
  document.addEventListener('livewire:navigated', initAuthUI);
})();
