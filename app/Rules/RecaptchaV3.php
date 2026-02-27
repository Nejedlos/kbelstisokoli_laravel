<?php

namespace App\Rules;

use App\Services\RecaptchaV3 as RecaptchaService;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class RecaptchaV3 implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    public function __construct(private readonly string $expectedAction)
    {
    }

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Pokud je reCAPTCHA vypnutá, pravidlo propouští.
        if (!config('recaptcha.enabled')) {
            return;
        }

        $token = (string) ($value ?? '');
        if ($token === '') {
            $fail(trans('recaptcha.missing_token'));
            return;
        }

        $service = app(RecaptchaService::class);
        $result = $service->verify($token, $this->expectedAction, request()->ip());

        if (!$result->passed) {
            // Rozlišení chybové hlášky
            if ($result->score !== null && $result->score < config('recaptcha.score_threshold')) {
                $fail(trans('recaptcha.low_score'));
                return;
            }

            if (empty($result->raw) && empty($result->errorCodes)) {
                $fail(trans('recaptcha.service_unavailable'));
                return;
            }

            $fail(trans('recaptcha.failed'));
        }
    }
}
