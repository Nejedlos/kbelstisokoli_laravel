<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CommunicationChannel: string implements HasLabel
{
    case Email = 'email';
    case Sms = 'sms';
    case Whatsapp = 'whatsapp';
    case Call = 'call';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Email => __('E-mail'),
            self::Sms => __('SMS'),
            self::Whatsapp => __('WhatsApp'),
            self::Call => __('Telefonát'),
        };
    }
}
