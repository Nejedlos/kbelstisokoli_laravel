<?php

namespace App\Filament\Widgets;

use App\Services\BrandingService;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Mail;

class ContactAdminWidget extends Widget
{
    protected string $view = 'filament.widgets.contact-admin-widget';

    // Priorita řazení widgetů na dashboardu (nižší = výš). Chceme druhý hned vedle uvítacího.
    protected static ?int $sort = -199;

    // Na menších displejích přes celou šířku, od md vedle sebe (poloviční šířka)
    protected int|string|array $columnSpan = [
        'md' => 1,
    ];

    // Vstupy formuláře (stack pod sebou)
    public ?string $senderName = null;
    public ?string $senderEmail = null;
    public ?string $senderPhone = null;
    public ?string $messageText = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->senderName = $user?->name;
        $this->senderEmail = $user?->email;
    }

    protected function getViewData(): array
    {
        $branding = app(BrandingService::class)->getSettings();

        $adminName = $branding['contact']['admin_name'] ?? ($branding['contact']['name'] ?? null);
        // Admin contact fields are stored under keys set via BrandingSettings page
        $db = $branding; // Already merged config + DB by service

        $contact = [
            'name' => $db['admin_contact_name'] ?? ($adminName ?? __('member.feedback.contact_card.admin_name_default')),
            'email' => $db['admin_contact_email'] ?? ($db['contact']['email'] ?? null),
            'phone' => $db['admin_contact_phone'] ?? ($db['contact']['phone'] ?? null),
            'photo' => $db['admin_contact_photo_path'] ?? null,
        ];

        // Prefer member route for a simple unified feedback form, works for logged-in trainers too
        $contactUrl = route('member.contact.admin.form');

        return compact('contact', 'contactUrl');
    }

    public function send(): void
    {
        $branding = app(BrandingService::class)->getSettings();
        $to = $branding['admin_contact_email'] ?? ($branding['contact']['email'] ?? null);

        if (!$to) {
            Notification::make()
                ->title('Kontakt administrátora není nastaven')
                ->danger()
                ->send();
            return;
        }

        if (!filled($this->messageText)) {
            Notification::make()
                ->title('Prosím, napište zprávu.')
                ->danger()
                ->send();
            return;
        }

        $subject = 'Zpráva z administrace';
        $body = sprintf(
            "Odesílatel: %s <%s>\nTelefon: %s\n\nZpráva:\n%s",
            $this->senderName ?? '-',
            $this->senderEmail ?? '-',
            $this->senderPhone ?? '-',
            $this->messageText ?? ''
        );

        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)
                    ->subject($subject);

                if ($this->senderEmail) {
                    $message->replyTo($this->senderEmail, $this->senderName ?? null);
                }
            });

            Notification::make()
                ->title('Zpráva byla odeslána')
                ->success()
                ->send();

            $this->reset(['messageText']);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Zprávu se nepodařilo odeslat')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
