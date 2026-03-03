<?php

namespace App\Filament\Resources\ExternalEntityMappings\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExternalEntityMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Externí identita')
                    ->columns(2)
                    ->schema([
                        TextInput::make('source_key')
                            ->label('Zdroj')
                            ->readOnly(),
                        TextInput::make('external_id')
                            ->label('Externí ID')
                            ->readOnly(),
                        TextInput::make('identity_key')
                            ->label('Klíč identity')
                            ->readOnly()
                            ->columnSpanFull(),
                        TextInput::make('metadata.player_name')
                            ->label('Jméno (z externu)')
                            ->readOnly(),
                        TextInput::make('metadata.birth_year')
                            ->label('Ročník (z externu)')
                            ->readOnly(),
                    ]),
                Section::make('Interní propojení')
                    ->schema([
                        Select::make('internal_id')
                            ->label('Přiřazený uživatel')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->helperText('Vyberte uživatele, kterému patří tyto statistiky'),
                    ]),
            ]);
    }
}
