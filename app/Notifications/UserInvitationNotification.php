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
        $clubName = $branding['club_name'];
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Pozvánka do členské sekce | '.$clubName)
            ->view('emails.notification', [
                'greeting' => 'Ahoj '.$notifiable->name.'!',
                'introLines' => [
                    'Byl vám vytvořen přístup do členské sekce basketbalového klubu '.$clubName.'.',
                    'Pro aktivaci účtu a nastavení hesla klikněte na tlačítko níže:',
                ],
                'actionText' => 'Nastavit přístupové heslo',
                'actionUrl' => $resetUrl,
                'outroLines' => [
                    'Tento odkaz je platný po omezenou dobu.',
                    'Pokud jste pozvánku nečekali, můžete tento e-mail ignorovat.',
                ],
                'salutation' => 'Tvůj tým '.$clubName,
            ]);
    }
}
