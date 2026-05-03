<?php

namespace App\Filament\Resources\Dmarc\DmarcMailboxResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MailboxForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní informace')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('status')
                            ->options([
                                'active' => 'Aktivní',
                                'disabled' => 'Vypnuto',
                            ])
                            ->default('active')
                            ->required(),
                    ]),

                Section::make('Nastavení IMAP')
                    ->columns(3)
                    ->schema([
                        TextInput::make('host')
                            ->default('mail.webglobe.cz')
                            ->required(),
                        TextInput::make('port')
                            ->numeric()
                            ->default(993)
                            ->required(),
                        Select::make('encryption')
                            ->options([
                                'ssl' => 'SSL',
                                'tls' => 'TLS',
                                'null' => 'Žádné',
                            ])
                            ->default('ssl')
                            ->required(),
                        TextInput::make('username')
                            ->required(),
                        TextInput::make('password')
                            ->password()
                            ->helperText('Heslo k e-mailové schránce. Podle konfigurace hostingu Webglobe obvykle stejné jako pro SMTP.')
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),
                    ]),
            ]);
    }
}
