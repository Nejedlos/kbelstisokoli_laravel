<?php

namespace App\Notifications;

use App\Models\Dmarc\DmarcAlertEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DmarcCriticalEventNotification extends Notification
{
    use Queueable;

    public function __construct(public DmarcAlertEvent $event)
    {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $severity = strtoupper($this->event->severity);
        $subject = "[DMARC {$severity}] {$this->event->domain} – {$this->event->source_ip}";

        $analysis = $this->event->payload;
        $spfStatus = ($analysis['spf_aligned'] ?? false) ? 'PASS' : 'FAIL';
        $dkimStatus = ($analysis['dkim_aligned'] ?? false) ? 'PASS' : 'FAIL';
        $dmarcStatus = ($analysis['dmarc_pass'] ?? false) ? 'PASS' : 'FAIL';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Byla detekována kritická DMARC událost.')
            ->line("Doména: {$this->event->domain}")
            ->line("Reporting organizace: {$this->event->report_org}")
            ->line("Zdrojová IP: {$this->event->source_ip}")
            ->line("Reverse DNS: " . ($analysis['reverse_dns'] ?? 'Neznámé'))
            ->line("SPF: {$spfStatus}")
            ->line("DKIM: {$dkimStatus}")
            ->line("DMARC: {$dmarcStatus}")
            ->line("Počet zpráv: {$this->event->occurrences}")
            ->line("Rizikové skóre: {$this->event->risk_score}/100")
            ->line("Význam:")
            ->line("E-mail neprošel důvěryhodnou autentizací. Při politice p=quarantine/reject by byl omezen nebo zahozen.")
            ->action('Zobrazit detail v aplikaci', url('/admin/dmarc-records')) // Přizpůsobíme podle realitou
            ->line('Prosím prověřte, zda je tento odesílatel legitimní.');
    }
}
