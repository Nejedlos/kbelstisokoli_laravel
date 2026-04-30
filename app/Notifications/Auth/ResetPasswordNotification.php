<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword as BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseNotification
{
    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('email_reset_subject'))
            ->greeting(__('email_reset_heading'))
            ->line(__('email_reset_body'))
            ->action(__('email_reset_button'), $this->resetUrl($notifiable))
            ->line(__('email_reset_footer'))
            ->salutation(__('email_regards'));
    }
}
