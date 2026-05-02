<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword as BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class ResetPasswordNotification extends BaseNotification
{
    /**
     * The password reset URL.
     *
     * @var string|null
     */
    public $url;

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->url;

        if (! $url) {
            $url = route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], true);

            // Robustní fix pro případy, kdy route() vygeneruje URL bez hosta (např. v CLI nebo špatném kontextu)
            if (! str_contains($url, '://') || str_contains($url, '://:/')) {
                $appUrl = config('app.url') ?: 'https://new.kbelstisokoli.cz'; // Hard fallback na produkční doménu projektu
                $appUrl = rtrim($appUrl, '/');
                $relativeUrl = route('password.reset', [
                    'token' => $this->token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ], false);

                $url = $appUrl . $relativeUrl;
            }
        }

        Log::channel('single')->info('DEBUG_MAIL: Preparing ResetPassword email', [
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
            'locale' => app()->getLocale(),
            'user_locale' => $notifiable->preferred_locale ?? 'n/a',
            'url' => $url,
            'url_source' => $this->url ? 'filament_property' : 'generated',
        ]);

        return (new MailMessage)
            ->subject(__('email_reset_subject'))
            ->greeting(__('email_reset_heading'))
            ->line(__('email_reset_body'))
            ->action(__('email_reset_button'), $url)
            ->line(__('email_reset_footer'))
            ->salutation(__('email_regards'));
    }
}
