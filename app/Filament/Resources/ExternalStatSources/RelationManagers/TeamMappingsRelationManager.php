<?php

namespace App\Filament\Resources\ExternalStatSources\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamMappingsRelationManager extends RelationManager
{
    protected static string $relationship = 'teamMappings';

    protected static ?string $title = 'Mapování týmů';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('team_id')
                    ->label('Tým')
                    ->relationship('team', 'name')
                    ->required(),
                TextInput::make('external_team_id')
                    ->label('Externí ID')
                    ->required(),
                TextInput::make('base_team_url')
                    ->label('Základní URL')
                    ->url()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('team.name')
            ->columns([
                TextColumn::make('team.name')
                    ->label('Tým')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('external_team_id')
                    ->label('Externí ID')
                    ->searchable(),
                TextColumn::make('base_team_url')
                    ->label('Základní URL')
                    ->limit(40)
                    ->url(fn ($record) => $record->base_team_url, true),
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
            ]);
    }
}
