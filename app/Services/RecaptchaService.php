<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Ověří token reCAPTCHA v3.
     */
    public function verify(string $token, ?string $ip = null): bool
    {
        $enabledSetting = Setting::where('key', 'recaptcha_enabled')->first();
        $enabled = $enabledSetting ? (bool) $enabledSetting->value : false;

        if (!$enabled) {
            return true;
        }

        $secretSetting = Setting::where('key', 'recaptcha_secret_key')->first();
        $secret = $secretSetting ? $secretSetting->value : null;

        $thresholdSetting = Setting::where('key', 'recaptcha_threshold')->first();
        $threshold = $thresholdSetting ? (float) $thresholdSetting->value : 0.5;

        if (!$secret || !$token) {
            Log::warning('reCAPTCHA verification failed: Missing secret or token.');
            return false;
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success']) {
                    return isset($data['score']) && $data['score'] >= $threshold;
                }
            }

            Log::error('reCAPTCHA API error:', $response->json() ?? []);
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification exception: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Získá site key pro frontend.
     */
    public function getSiteKey(): ?string
    {
        $setting = Setting::where('key', 'recaptcha_site_key')->first();
        return $setting ? $setting->value : null;
    }

    /**
     * Je reCAPTCHA aktivní?
     */
    public function isEnabled(): bool
    {
        $setting = Setting::where('key', 'recaptcha_enabled')->first();
        return $setting ? (bool) $setting->value : false;
    }
}
