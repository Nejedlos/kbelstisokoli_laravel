<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaV3
{
    /**
     * Ověří token reCAPTCHA v3 u Google API.
     */
    public function verify(string $token, string $expectedAction, ?string $ip = null): RecaptchaResult
    {
        if (! config('recaptcha.enabled')) {
            return new RecaptchaResult(passed: true);
        }

        $secret = config('recaptcha.secret_key');

        if (! $secret) {
            Log::error('reCAPTCHA secret key is missing in configuration.');

            return new RecaptchaResult(passed: app()->isLocal());
        }

        if (! $token) {
            return new RecaptchaResult(passed: false, errorCodes: ['missing-input-response']);
        }

        try {
            $response = Http::asForm()
                ->timeout(config('recaptcha.timeout', 3))
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $ip,
                ]);

            if ($response->failed()) {
                Log::warning('reCAPTCHA API request failed: '.$response->status());

                return new RecaptchaResult(passed: app()->isLocal(), raw: $response->json() ?? []);
            }

            $data = $response->json();

            $success = $data['success'] ?? false;
            $score = $data['score'] ?? null;
            $action = $data['action'] ?? null;
            $errorCodes = $data['error-codes'] ?? [];

            $passed = $success &&
                ($action === null || $action === $expectedAction) &&
                ($score === null || $score >= config('recaptcha.score_threshold', 0.5));

            if (! $passed && config('app.debug')) {
                Log::debug('reCAPTCHA failed', [
                    'expected_action' => $expectedAction,
                    'received_action' => $action,
                    'score' => $score,
                    'threshold' => config('recaptcha.score_threshold'),
                    'error_codes' => $errorCodes,
                ]);
            }

            return new RecaptchaResult(
                passed: $passed,
                score: $score,
                errorCodes: $errorCodes,
                raw: $data
            );

        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification exception: '.$e->getMessage());

            // Fail closed v produkci, fail open v localu
            return new RecaptchaResult(passed: app()->isLocal());
        }
    }
}
