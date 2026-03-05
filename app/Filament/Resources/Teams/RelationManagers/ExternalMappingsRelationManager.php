<?php

namespace App\Filament\Resources\Teams\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use App\Support\IconHelper;

class ExternalMappingsRelationManager extends RelationManager
{
    protected static string $relationship = 'externalMappings';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('source_key')
                    ->label('Zdroj')
                    ->default('czbasketball')
                    ->required()
                    ->maxLength(255),
                TextInput::make('external_team_id')
                    ->label('Externí ID týmu')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Např. 7738'),
                TextInput::make('base_team_url')
                    ->label('Základní URL týmu')
                    ->required()
                    ->url()
                    ->maxLength(255)
                    ->helperText('https://cz.basketball/tym/7738'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('external_team_id')
            ->columns([
                TextColumn::make('source_key')
                    ->label('Zdroj')
                    ->badge(),
                TextColumn::make('external_team_id')
                    ->label('Externí ID')
                    ->searchable(),
                TextColumn::make('base_team_url')
                    ->label('URL')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Přidat externí mapování')
                    ->icon(new HtmlString(IconHelper::render(IconHelper::CREATE))),
            ])
            ->recordActions([
                EditAction::make()
                    ->icon(new HtmlString(IconHelper::render(IconHelper::EDIT))),
                DeleteAction::make()
                    ->icon(new HtmlString(IconHelper::render(IconHelper::DELETE))),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
