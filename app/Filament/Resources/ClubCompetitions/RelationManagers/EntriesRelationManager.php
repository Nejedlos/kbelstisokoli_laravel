<?php

namespace App\Filament\Resources\ClubCompetitions\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'entries';

    protected static ?string $title = 'Výsledky / Leaderboard';

    protected static ?string $modelLabel = 'Záznam';

    protected static ?string $pluralModelLabel = 'Záznamy výsledků';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('player_id')
                            ->label('Hráč')
                            ->relationship('player', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('teams')
                            ->label(__('admin.navigation.resources.team.plural_label'))
                            ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ]),
                TextInput::make('label')
                    ->label('Vlastní štítek')
                    ->helperText('Použijte pro účastníky mimo DB.'),
                Grid::make(2)
                    ->schema([
                        TextInput::make('value')
                            ->label('Hodnota / Skóre')
                            ->numeric()
                            ->required(),
                        Select::make('value_type')
                            ->label('Typ zápisu')
                            ->options([
                                'incremental' => 'Přičíst k celku',
                                'absolute' => 'Absolutní hodnota (celkem)',
                            ])
                            ->default('absolute'),
                    ]),
                TextInput::make('source_note')
                    ->label('Zdroj / Poznámka')
                    ->placeholder('např. Zápas proti Sokolu'),
                Select::make('basketball_match_id')
                    ->label('Vazba na zápas')
                    ->relationship('match', 'scheduled_at') // scheduled_at není ideální pro label, ale pro skeleton stačí
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('rank')
                    ->label('Pořadí')
                    ->state(fn ($rowLoop) => $rowLoop->iteration),
                TextColumn::make('displayName')
                    ->label('Účastník')
                    ->state(fn ($record) => $record->player?->name ?? ($record->teams->count() ? $record->teams->pluck('name')->join(', ') : null) ?? $record->label)
                    ->searchable(['label']),
                TextColumn::make('value')
                    ->label('Hodnota')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('source_note')
                    ->label('Poznámka')
                    ->limit(30),
                TextColumn::make('updated_at')
                    ->label('Datum')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('value', 'desc');
    }
}
