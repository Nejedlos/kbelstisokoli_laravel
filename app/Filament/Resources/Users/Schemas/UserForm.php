<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní údaje')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Jméno a příjmení')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('E-mailová adresa')
                                    ->email()
                                    ->required()
                                    ->unique('users', 'email', ignoreRecord: true)
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('Telefonní číslo')
                                    ->tel()
                                    ->maxLength(255),
                                Select::make('roles')
                                    ->label('Role uživatele')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),
                            ]),
                    ]),

                Section::make('Zabezpečení')
                    ->description('Ponechte heslo prázdné, pokud jej nechcete měnit.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('password')
                                    ->label('Heslo')
                                    ->password()
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                    ->maxLength(255),
                                Toggle::make('is_active')
                                    ->label('Aktivní účet')
                                    ->helperText('Deaktivovaný uživatel se nebude moci přihlásit.')
                                    ->default(true),
                            ]),
                    ]),

                Section::make('Administrativní poznámka')
                    ->collapsed()
                    ->schema([
                        Textarea::make('admin_note')
                            ->label('Interní poznámka k uživateli')
                            ->placeholder('Zadejte interní info o členovi...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
