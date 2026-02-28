<?php

namespace App\Notifications;

use App\Services\BrandingService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification
{
    use Queueable;

    /**
     * Získá data pro in-app notifikaci.
     */
    abstract protected function getNotificationData(): array;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (method_exists($notifiable, 'prefersNotification') && $notifiable->prefersNotification('general', 'mail')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $branding = app(BrandingService::class)->getSettings();
        $data = $this->getNotificationData();

        return (new MailMessage)
            ->subject($data['title'] ?? 'Upozornění | '.$branding['club_name'])
            ->greeting('Ahoj '.$notifiable->name.'!')
            ->line($data['message'] ?? '')
            ->action($data['action_label'] ?? 'Zobrazit v portálu', $data['action_url'] ?? route('member.dashboard'))
            ->line('Děkujeme, že jsi součástí týmu!')
            ->salutation('Tvůj tým '.$branding['club_name']);
    }

    /**
     * Get the array representation of the notification for database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->getNotificationData();
    }
}
