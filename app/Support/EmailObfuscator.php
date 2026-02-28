<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;

class EmailObfuscator
{
    /**
     * Zakóduje e-mailovou adresu pro použití v URL.
     */
    public static function encode(string $email): string
    {
        return base64_encode($email);
    }

    /**
     * Dekóduje e-mailovou adresu.
     */
    public static function decode(string $encodedEmail): ?string
    {
        try {
            $decoded = base64_decode($encodedEmail, true);
            if (!$decoded || !filter_var($decoded, FILTER_VALIDATE_EMAIL)) {
                return null;
            }
            return $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Vygeneruje odkaz na kontaktní formulář pro daný e-mail.
     */
    public static function getContactUrl(string $email): string
    {
        if (\Illuminate\Support\Facades\Route::has('public.contact-form')) {
            return route('public.contact-form', ['to' => self::encode($email)]);
        }

        return 'mailto:'.$email;
    }
}
