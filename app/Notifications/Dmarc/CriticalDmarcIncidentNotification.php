<?php

namespace App\Notifications\Dmarc;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CriticalDmarcIncidentNotification extends Notification
{
    use Queueable;

    public function __construct(public $incident)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('DMARC ALERT: ' . $this->incident->domain . ' [' . $this->incident->source_ip . ']')
            ->greeting('Dobrý den,')
            ->line('Byl detekován kritický problém s doručitelností e-mailů (DMARC fail).')
            ->line('**Doména:** ' . $this->incident->domain)
            ->line('**Zdrojová IP:** ' . $this->incident->source_ip)
            ->line('**Popis:** ' . $this->incident->description)
            ->line('**Doporučená akce:** ' . $this->incident->recommended_action)
            ->action('Zobrazit incident v administraci', url(config('app.url') . '/admin/dmarc-incidents/' . $this->incident->id))
            ->line('Tento incident byl automaticky vygenerován na základě DMARC aggregate reportů.');
    }
}
