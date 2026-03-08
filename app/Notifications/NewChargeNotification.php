<?php

namespace App\Notifications;

use App\Models\FinanceCharge;
use Illuminate\Notifications\Messages\MailMessage;

class NewChargeNotification extends BaseNotification
{
    protected string $notificationType = 'finance';

    /**
     * Create a new notification instance.
     */
    public function __construct(public FinanceCharge $charge) {}

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $branding = app(\App\Services\BrandingService::class)->getSettings();
        $clubName = $branding['club_name'];

        return (new MailMessage)
            ->subject(__('member.notifications.mail.new_charge_subject', ['title' => $this->charge->title]))
            ->view('emails.notification', [
                'greeting' => __('member.notifications.mail.greeting', ['name' => $notifiable->name]),
                'introLines' => [
                    __('member.notifications.new_charge_message', [
                        'title' => $this->charge->title,
                        'amount' => number_format($this->charge->amount_total, 0, ',', ' '),
                    ]),
                    __('member.notifications.mail.charge_item', ['title' => $this->charge->title]),
                    __('member.notifications.mail.charge_amount', ['amount' => number_format($this->charge->amount_total, 0, ',', ' ')]),
                    __('member.notifications.mail.charge_due_date', [
                        'date' => $this->charge->due_date ? $this->charge->due_date->format('d. m. Y') : __('member.notifications.mail.charge_due_date_unknown'),
                    ]),
                ],
                'actionText' => __('member.notifications.view_payments'),
                'actionUrl' => route('member.economy.index'),
                'outroLines' => [
                    __('member.notifications.mail.charge_please_pay'),
                    __('member.notifications.mail.footer'),
                ],
                'salutation' => __('member.notifications.mail.salutation', ['club' => $clubName]),
            ]);
    }

    /**
     * Povinná metoda pro BaseNotification
     */
    protected function getNotificationData(): array
    {
        return [
            'charge_id' => $this->charge->id,
            'title' => 'member.notifications.new_charge_title',
            'message' => __('member.notifications.new_charge_message', [
                'title' => $this->charge->title,
                'amount' => number_format($this->charge->amount_total, 0, ',', ' '),
            ]),
            'action_label' => 'member.notifications.view_payments',
            'action_url' => route('member.economy.index'),
            'type' => 'finance',
        ];
    }
}
