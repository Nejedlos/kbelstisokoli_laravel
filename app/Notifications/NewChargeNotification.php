<?php

namespace App\Notifications;

use App\Models\FinanceCharge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewChargeNotification extends BaseNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public FinanceCharge $charge)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->prefersNotification('finance', 'mail')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nový platební předpis: ' . $this->charge->title)
            ->greeting('Ahoj ' . $notifiable->name . '!')
            ->line('Byl ti vystaven nový platební předpis v klubovém systému ###TEAM_NAME###.')
            ->line('Položka: ' . $this->charge->title)
            ->line('Částka: ' . number_format($this->charge->amount_total, 0, ',', ' ') . ' Kč')
            ->line('Splatnost: ' . ($this->charge->due_date ? $this->charge->due_date->format('d. m. Y') : 'neuvedeno'))
            ->action('Zobrazit moje platby', route('member.economy.index'))
            ->line('Prosím o včasnou úhradu. Děkujeme!');
    }

    /**
     * Povinná metoda pro BaseNotification
     */
    protected function getNotificationData(): array
    {
        return [
            'charge_id' => $this->charge->id,
            'title' => 'Nový platební předpis',
            'message' => "Byl ti vystaven předpis '{$this->charge->title}' na částku " . number_format($this->charge->amount_total, 0, ',', ' ') . " Kč.",
            'action_url' => route('member.economy.index'),
            'type' => 'finance',
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'charge_id' => $this->charge->id,
            'title' => 'Nový platební předpis',
            'message' => "Byl ti vystaven předpis '{$this->charge->title}' na částku {$this->charge->amount_total} Kč.",
            'action_url' => route('member.economy.index'),
            'type' => 'finance',
        ];
    }
}
