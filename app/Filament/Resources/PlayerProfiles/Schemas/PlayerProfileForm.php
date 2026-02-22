<?php

namespace App\Filament\Resources\PlayerProfiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PlayerProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní údaje hráče')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('Uživatel (Účet)')
                                    ->relationship('user', 'name', fn ($query) => $query->whereDoesntHave('playerProfile'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Vyberte uživatele, pro kterého profil zakládáte. Zobrazeni jsou pouze uživatelé bez profilu.')
                                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),

                                Select::make('user_id')
                                    ->label('Uživatel (Účet)')
                                    ->relationship('user', 'name')
                                    ->disabled()
                                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),

                                TextInput::make('jersey_number')
                                    ->label('Číslo dresu')
                                    ->placeholder('např. 23')
                                    ->maxLength(5),

                                Select::make('position')
                                    ->label('Pozice')
                                    ->options([
                                        'PG' => 'PG - Rozehrávač',
                                        'SG' => 'SG - Křídlo (2)',
                                        'SF' => 'SF - Křídlo (3)',
                                        'PF' => 'PF - Pivot (4)',
                                        'C' => 'C - Pivot (5)',
                                    ])
                                    ->searchable(),

                                Toggle::make('is_active')
                                    ->label('Aktivní hráč status')
                                    ->default(true),
                            ]),
                    ]),

                Section::make('Týmová příslušnost')
                    ->schema([
                        Select::make('teams')
                            ->label('Přiřazené týmy')
                            ->relationship('teams', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),

                Section::make('Bio a poznámky')
                    ->schema([
                        Textarea::make('public_bio')
                            ->label('Veřejné bio')
                            ->helperText('Zobrazuje se na webu u profilu hráče.')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('private_note')
                            ->label('Interní poznámka (trenér)')
                            ->helperText('Pouze pro interní potřeby klubu.')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
