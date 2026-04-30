<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword as BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class ResetPasswordNotification extends BaseNotification
{
    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        Log::channel('single')->info('DEBUG_MAIL: Preparing ResetPassword email', [
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
            'url' => $this->resetUrl($notifiable),
        ]);

        return (new MailMessage)
            ->subject(__('email_reset_subject'))
            ->greeting(__('email_reset_heading'))
            ->line(__('email_reset_body'))
            ->action(__('email_reset_button'), $this->resetUrl($notifiable))
            ->line(__('email_reset_footer'))
            ->salutation(__('email_regards'));
    }
}
