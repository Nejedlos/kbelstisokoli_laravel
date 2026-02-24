<?php

namespace App\Filament\Widgets;

use App\Services\BrandingService;
use Filament\Widgets\Widget;

class ContactAdminWidget extends Widget
{
    protected string $view = 'filament.widgets.contact-admin-widget';

    // Na menších displejích přes celou šířku, od md vedle sebe (poloviční šířka)
    protected int|string|array $columnSpan = [
        'md' => 1,
    ];

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
}
