@props([
    'formId' => null,
    'action' => 'generic',
])

@php
    $enabled = (bool) config('recaptcha.enabled');
    $siteKey = config('recaptcha.site_key');
    if (!$enabled || !$siteKey) {
        // Nevykresluj nic, pokud není aktivní nebo chybí klíč
        return;
    }

    // Vytvoř unikátní ID pro input i pro guard
    $uid = $formId ? (string) $formId : ('recaptcha-form-' . uniqid());
@endphp

<input type="hidden" name="g-recaptcha-response" id="{{ $uid }}-grecaptcha-token" value="">

@push('scripts')
<script src="https://www.google.com/recaptcha/api.js?render={{ $siteKey }}" defer></script>
<script>
    window.__recaptchaBound = window.__recaptchaBound || {};

    function bindRecaptchaForForm_{{ Str::slug($uid, '_') }}() {
        const form = document.getElementById(@json($formId ?? $uid));
        const input = document.getElementById(@json($uid + '-grecaptcha-token'));
        if (!form || !input) return;
        if (window.__recaptchaBound[@json($uid)]) return; // Guard proti duplicitám

        window.__recaptchaBound[@json($uid)] = true;

        form.addEventListener('submit', async function (e) {
            // Pokud už máme token mladší než ~100s, můžeme zkusit použít (v3 nevrací exp time, držme krátce)
            if (!window.grecaptcha) return; // fallback – necháme backend rozhodnout
            e.preventDefault();

            grecaptcha.ready(function () {
                grecaptcha.execute(@json($siteKey), { action: @json($action) }).then(function (token) {
                    input.value = token || '';
                    form.submit();
                });
            });
        }, { capture: true });
    }

    document.addEventListener('DOMContentLoaded', bindRecaptchaForForm_{{ Str::slug($uid, '_') }});
    document.addEventListener('livewire:navigated', bindRecaptchaForForm_{{ Str::slug($uid, '_') }});
</script>
<style>.grecaptcha-badge{visibility:visible!important}</style>
@endpush
