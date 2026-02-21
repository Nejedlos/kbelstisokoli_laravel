<?php

namespace App\Filament\Resources\ClubEvents\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class AttendancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    protected static ?string $title = 'Docházka / RSVP';

    protected static ?string $modelLabel = 'Záznam docházky';

    protected static ?string $pluralModelLabel = 'Záznamy docházky';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Člen')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('status')
                    ->label('Stav účasti')
                    ->options([
                        'pending' => 'Čeká na vyjádření',
                        'confirmed' => 'Potvrzeno (Přijde)',
                        'declined' => 'Omluveno (Nepřijde)',
                        'maybe' => 'Možná',
                    ])
                    ->default('pending')
                    ->required(),
                Textarea::make('note')
                    ->label('Poznámka člena / Důvod omluvenky')
                    ->placeholder('Zadáno členem...')
                    ->rows(2),
                Textarea::make('internal_note')
                    ->label('Interní poznámka (pro trenéry)')
                    ->placeholder('Zadáno trenérem...')
                    ->rows(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Člen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'confirmed' => 'success',
                        'declined' => 'danger',
                        'maybe' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Čeká',
                        'confirmed' => 'Přijde',
                        'declined' => 'Nepřijde',
                        'maybe' => 'Možná',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('note')
                    ->label('Poznámka')
                    ->limit(30)
                    ->toggleable(),
                TextColumn::make('internal_note')
                    ->label('Interní pozn.')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('responded_at')
                    ->label('Odpovězeno')
                    ->dateTime('d.m. H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stav RSVP')
                    ->options([
                        'pending' => 'Čeká',
                        'confirmed' => 'Přijde',
                        'declined' => 'Nepřijde',
                        'maybe' => 'Možná',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Přidat člena ručně'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->label('Odebrat'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
