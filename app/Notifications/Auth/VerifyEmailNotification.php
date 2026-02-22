<?php

namespace App\Notifications\Auth;

use Filament\Auth\Notifications\VerifyEmail as BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends BaseNotification
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('email_verify_subject'))
            ->greeting(__('email_verify_heading'))
            ->line(__('email_verify_body'))
            ->action(__('email_verify_button'), $this->url)
            ->salutation(__('email_regards'));
    }
}
