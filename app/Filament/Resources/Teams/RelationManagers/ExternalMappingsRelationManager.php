<?php

namespace App\Filament\Resources\Teams\RelationManagers;

use App\Filament\Resources\ExternalStatSources\ExternalStatSourceResource;
use App\Models\ExternalStatSource;
use App\Support\IconHelper;
use App\Support\Icons\AppIcon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ExternalMappingsRelationManager extends RelationManager
{
    protected static string $relationship = 'externalMappings';

    protected static ?string $title = 'Externí zdroje statistik';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('source_key')
                    ->label('Zdroj')
                    ->options(ExternalStatSource::all()->pluck('name', 'slug'))
                    ->searchable()
                    ->required(),
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
                TextColumn::make('externalStatSource.name')
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
                Action::make('go_to_source')
                    ->label('Upravit ve zdroji')
                    ->icon(new HtmlString(IconHelper::render(AppIcon::GLOBE)))
                    ->url(fn ($record) => $record->externalStatSource
                        ? ExternalStatSourceResource::getUrl('edit', ['record' => $record->externalStatSource->id]).'?activeRelation=0'
                        : null)
                    ->openUrlInNewTab(),
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
