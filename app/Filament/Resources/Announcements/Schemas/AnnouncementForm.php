<?php

namespace App\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Obsah oznámení')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title.cs')
                                    ->label('Štítek / Titulek (CZ)')
                                    ->placeholder('např. DŮLEŽITÉ, INFO, ZMĚNA')
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('title.en')
                                    ->label('Label / Title (EN)')
                                    ->placeholder('e.g. IMPORTANT, INFO, UPDATE')
                                    ->required()
                                    ->maxLength(50),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Textarea::make('message.cs')
                                    ->label('Text oznámení (CZ)')
                                    ->placeholder('Zadejte text, který se zobrazí v horní liště.')
                                    ->required()
                                    ->rows(2)
                                    ->maxLength(255),
                                Textarea::make('message.en')
                                    ->label('Message (EN)')
                                    ->placeholder('Enter the announcement text.')
                                    ->required()
                                    ->rows(2)
                                    ->maxLength(255),
                            ]),
                    ]),

                Section::make('Odkaz (CTA)')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('cta_label.cs')
                                            ->label('Text tlačítka (CZ)')
                                            ->placeholder('Zjistit více'),
                                        TextInput::make('cta_label.en')
                                            ->label('Button text (EN)')
                                            ->placeholder('Find out more'),
                                    ]),
                                TextInput::make('cta_url')
                                    ->label('Odkaz (URL)')
                                    ->placeholder('/kontakt nebo https://...'),
                            ]),
                    ]),

                Section::make('Nastavení doručení')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('audience')
                                    ->label('Cílová skupina')
                                    ->options([
                                        'public' => 'Pouze veřejný web',
                                        'member' => 'Pouze členská sekce',
                                        'both' => 'Všichni (veřejný i členský)',
                                    ])
                                    ->default('both')
                                    ->required(),
                                Select::make('style_variant')
                                    ->label('Styl / Naléhavost')
                                    ->options([
                                        'info' => 'Modrá (Info)',
                                        'success' => 'Zelená (Úspěch)',
                                        'warning' => 'Žlutá (Varování)',
                                        'urgent' => 'Červená (Urgentní)',
                                    ])
                                    ->default('info')
                                    ->required(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label('Platné od')
                                    ->native(false),
                                DateTimePicker::make('ends_at')
                                    ->label('Platné do')
                                    ->native(false),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('priority')
                                    ->label('Priorita')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Vyšší číslo znamená dřívější zobrazení.'),
                                Toggle::make('is_active')
                                    ->label('Aktivní')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
