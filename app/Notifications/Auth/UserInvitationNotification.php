<?php

namespace App\Notifications\Auth;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class UserInvitationNotification extends ResetPasswordNotification
{
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
                $appUrl = config('app.url') ?: 'https://kbelstisokoli.cz';
                $appUrl = rtrim($appUrl, '/');
                $relativeUrl = route('password.reset', [
                    'token' => $this->token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ], false);

                $url = $appUrl . $relativeUrl;
            }
        }

        Log::channel('single')->warning('DEBUG_MAIL: Preparing UserInvitation email', [
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
            'locale' => app()->getLocale(),
            'url' => $url,
        ]);

        return (new MailMessage)
            ->subject(__('email_invitation_subject'))
            ->greeting(__('email_invitation_heading', ['name' => $notifiable->first_name ?: $notifiable->name]))
            ->line(__('email_invitation_body'))
            ->action(__('email_invitation_button'), $url)
            ->line(__('email_invitation_footer'))
            ->salutation(__('email_regards'));
    }
}
