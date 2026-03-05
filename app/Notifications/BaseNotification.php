<?php

namespace App\Notifications;

use App\Services\BrandingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Typ notifikace pro preference.
     */
    protected string $notificationType = 'general';

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

        if (method_exists($notifiable, 'prefersNotification') && $notifiable->prefersNotification($this->notificationType, 'mail')) {
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
        $clubName = $branding['club_name'];
        $data = $this->getNotificationData();

        $subject = ! empty($data['title'])
            ? (__($data['title']).' | '.$clubName)
            : __('member.notifications.mail.subject_default', ['club' => $clubName]);

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting(__('member.notifications.mail.greeting', ['name' => $notifiable->name]))
            ->line(! empty($data['message']) ? $data['message'] : '')
            ->action(
                ! empty($data['action_label']) ? __($data['action_label']) : __('member.notifications.mail.view_portal'),
                $data['action_url'] ?? route('member.dashboard')
            )
            ->line(__('member.notifications.mail.footer'))
            ->salutation(__('member.notifications.mail.salutation', ['club' => $clubName]));

        return $mail;
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
