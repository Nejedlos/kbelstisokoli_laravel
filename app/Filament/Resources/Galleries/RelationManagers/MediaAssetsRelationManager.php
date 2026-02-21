<?php

namespace App\Filament\Resources\Galleries\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\IconColumn;

class MediaAssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'mediaAssets';

    protected static ?string $title = 'Média v galerii';

    protected static ?string $modelLabel = 'Asset';

    protected static ?string $pluralModelLabel = 'Média';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('caption_override')
                    ->label('Vlastní popisek pro tuto galerii')
                    ->placeholder('Ponechte prázdné pro použití výchozího z knihovny'),
                Toggle::make('is_visible')
                    ->label('Viditelné')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                SpatieMediaLibraryImageColumn::make('file')
                    ->label('Náhled')
                    ->collection('default')
                    ->conversion('thumb')
                    ->circular(),

                TextColumn::make('title')
                    ->label('Název v knihovně')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('caption_override')
                    ->label('Override popisek')
                    ->placeholder('Výchozí'),

                IconColumn::make('is_visible')
                    ->label('Viditelné')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('sort_order')->numeric()->default(0),
                    ])
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order');
    }
}
