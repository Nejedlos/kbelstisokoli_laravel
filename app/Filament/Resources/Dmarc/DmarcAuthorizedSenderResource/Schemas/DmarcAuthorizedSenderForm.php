<?php

namespace App\Filament\Resources\Dmarc\DmarcAuthorizedSenderResource\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class DmarcAuthorizedSenderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní informace')
                    ->columns(2)
                    ->schema([
                        TextInput::make('domain_name')
                            ->label('Doména (sledovaná)')
                            ->placeholder('kbelstisokoli.cz')
                            ->required(),
                        TextInput::make('name')
                            ->label('Název odesílatele')
                            ->placeholder('Seznam SMTP / Google Workspace')
                            ->required(),
                        Select::make('sender_type')
                            ->label('Typ odesílatele')
                            ->options([
                                'internal' => 'Interní server',
                                'hosting' => 'Webhosting',
                                'smtp_provider' => 'SMTP poskytovatel',
                                'newsletter' => 'Newsletter služba',
                                'google_workspace' => 'Google Workspace',
                                'microsoft_365' => 'Microsoft 365',
                                'other' => 'Jiný',
                            ])
                            ->required()
                            ->default('other'),
                        Toggle::make('is_active')
                            ->label('Aktivní')
                            ->default(true)
                            ->required(),
                    ]),

                Section::make('Autorizační pravidla')
                    ->columns(2)
                    ->description('Definujte identifikátory, podle kterých systém pozná tohoto odesílatele.')
                    ->schema([
                        TagsInput::make('allowed_ips')
                            ->label('Povolené IP adresy'),
                        TagsInput::make('allowed_cidrs')
                            ->label('Povolené rozsahy (CIDR)'),
                        TagsInput::make('allowed_spf_domains')
                            ->label('Povolené SPF domény'),
                        TagsInput::make('allowed_dkim_domains')
                            ->label('Povolené DKIM domény'),
                        TagsInput::make('allowed_dkim_selectors')
                            ->label('Povolené DKIM selektory'),
                        TagsInput::make('expected_header_from_domains')
                            ->label('Očekávané Header From domény'),
                    ]),

                Section::make('Poznámky a historie')
                    ->schema([
                        Textarea::make('description')
                            ->label('Popis'),
                        Textarea::make('notes')
                            ->label('Interní poznámky'),
                        DateTimePicker::make('last_seen_at')
                            ->label('Naposledy viděn')
                            ->disabled(),
                    ]),
            ]);
    }
}
