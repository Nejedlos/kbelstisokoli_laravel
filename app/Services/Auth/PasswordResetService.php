<?php

namespace App\Services\Auth;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    /**
     * Pošle odkaz pro reset hesla.
     */
    public function sendResetLink(string $email, ?string $panel = null): string
    {
        $email = Str::lower(trim($email));
        $ip = request()->ip();

        // Rate limiting
        $this->checkRateLimit($email, $ip);

        $broker = Password::broker();

        $status = $broker->sendResetLink(
            ['email' => $email],
            function ($user, $token) use ($panel) {
                // Použijeme sjednocenou notifikaci zaregistrovanou v AppServiceProvider
                $notification = app(\Filament\Auth\Notifications\ResetPassword::class, ['token' => $token]);

                if ($panel) {
                    $notification->url = Filament::getPanel($panel)->getResetPasswordUrl($token, $user);
                } else {
                    $notification->url = route('password.reset', [
                        'token' => $token,
                        'email' => $user->getEmailForPasswordReset(),
                    ]);
                }

                $user->notify($notification);

                event(new PasswordResetLinkSent($user));
            }
        );

        $this->logRequest($email, $status, $ip);

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => [__('passwords.throttled')],
            ]);
        }

        // Vždy vracíme success status pro UI, pokud je to potřeba v controlleru,
        // ale služba sama vrací reálný status pro interní potřebu.
        return $status;
    }

    /**
     * Kontrola rate limitu pro email i IP.
     */
    protected function checkRateLimit(string $email, string $ip): void
    {
        $emailHash = md5($email);
        $ipHash = md5($ip);

        // Per email: 1 pokus / 60 sekund (shoda s Laravel brokerem pro zamezení enumerace)
        if (RateLimiter::tooManyAttempts("pw-reset-email-throttle:{$emailHash}", 1)) {
            $seconds = RateLimiter::availableIn("pw-reset-email-throttle:{$emailHash}");
            throw ValidationException::withMessages([
                'email' => [__('passwords.throttled') . ' ' . __('passwords.throttled_seconds', ['seconds' => $seconds])],
            ]);
        }

        // Per email: 3 pokusy / 15 minut (dlouhodobý limit)
        if (RateLimiter::tooManyAttempts("pw-reset-email:{$emailHash}", 3)) {
            $this->logRateLimit($email, 'email', $ip);
            $seconds = RateLimiter::availableIn("pw-reset-email:{$emailHash}");
            $minutes = ceil($seconds / 60);
            throw ValidationException::withMessages([
                'email' => [__('passwords.throttled') . ' ' . __('passwords.throttled_minutes', ['minutes' => $minutes])],
            ]);
        }

        // Per IP: 10 pokusů / 15 minut
        if (RateLimiter::tooManyAttempts("pw-reset-ip:{$ipHash}", 10)) {
            $this->logRateLimit($email, 'ip', $ip);
            $seconds = RateLimiter::availableIn("pw-reset-ip:{$ipHash}");
            $minutes = ceil($seconds / 60);
            throw ValidationException::withMessages([
                'email' => [__('passwords.throttled') . ' ' . __('passwords.throttled_minutes', ['minutes' => $minutes])],
            ]);
        }

        RateLimiter::hit("pw-reset-email-throttle:{$emailHash}", 60);
        RateLimiter::hit("pw-reset-email:{$emailHash}", 900);
        RateLimiter::hit("pw-reset-ip:{$ipHash}", 900);
    }

    protected function logRequest(string $email, string $status, string $ip): void
    {
        $user = User::where('email', $email)->first();

        Log::info('Password reset requested', [
            'email_hash' => hash('sha256', $email),
            'user_exists' => (bool) $user,
            'status' => $status,
            'ip' => $ip,
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected function logRateLimit(string $email, string $type, string $ip): void
    {
        Log::warning('Password reset rate limited', [
            'email_hash' => hash('sha256', $email),
            'type' => $type,
            'ip' => $ip,
        ]);
    }
}
