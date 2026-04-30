<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail as BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends BaseNotification
{
    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('email_verify_subject'))
            ->greeting(__('email_verify_heading'))
            ->line(__('email_verify_body'))
            ->action(__('email_verify_button'), $this->verificationUrl($notifiable))
            ->salutation(__('email_regards'));
    }
}
