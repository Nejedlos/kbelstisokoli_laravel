<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserInvitationNotification extends BaseNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $token) {}

    /**
     * Získá data pro in-app notifikaci.
     */
    protected function getNotificationData(): array
    {
        return [
            'title' => 'Vítejte v klubu!',
            'message' => 'Váš účet byl vytvořen. Prosím, nastavte si přístupové heslo.',
            'action_label' => 'Nastavit heslo',
            'action_url' => url(route('password.reset', [
                'token' => $this->token,
                'email' => '', // Email se doplní v reset formuláři nebo přes query
            ], false)),
            'type' => 'invitation',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $branding = app(\App\Services\BrandingService::class)->getSettings();
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Pozvánka do členské sekce | '.$branding['club_name'])
            ->greeting('Ahoj '.$notifiable->name.'!')
            ->line('Byl vám vytvořen přístup do členské sekce basketbalového klubu '.$branding['club_name'].'.')
            ->line('Pro aktivaci účtu a nastavení hesla klikněte na tlačítko níže:')
            ->action('Nastavit přístupové heslo', $resetUrl)
            ->line('Tento odkaz je platný po omezenou dobu.')
            ->line('Pokud jste pozvánku nečekali, můžete tento e-mail ignorovat.')
            ->salutation('Tvůj tým '.$branding['club_name']);
    }
}
