<?php

namespace App\Filament\Resources\LegacyImportBatches\RelationManagers;

use App\Services\Stats\Legacy\Extractors\LegacyStatExtractor;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class FilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';

    protected static ?string $title = 'Soubory v dávce';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('original_filename')
                    ->label('Název souboru')
                    ->readOnly(),
                TextInput::make('detected_season_label')
                    ->label('Zjištěná sezóna')
                    ->readOnly(),
                TextInput::make('detected_team_slug')
                    ->label('Zjištěný tým')
                    ->readOnly(),
                TextInput::make('file_type')
                    ->label('Typ souboru')
                    ->readOnly(),
                TextInput::make('status')
                    ->label('Stav')
                    ->readOnly(),
                TextInput::make('imported_rows_count')
                    ->label('Importováno řádků')
                    ->readOnly(),
                Textarea::make('error_summary')
                    ->label('Chyba / Log')
                    ->columnSpanFull()
                    ->readOnly(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_filename')
            ->columns([
                TextColumn::make('original_filename')
                    ->label('Soubor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('detected_season_label')
                    ->label('Sezóna')
                    ->sortable(),

                TextColumn::make('detected_team_slug')
                    ->label('Tým')
                    ->sortable(),

                TextColumn::make('file_type')
                    ->label('Typ')
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'queued' => 'gray',
                        'running' => 'info',
                        'success' => 'success',
                        'failed' => 'danger',
                        'skipped' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('imported_rows_count')
                    ->label('Řádky')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('warnings_count')
                    ->label('Varování')
                    ->numeric()
                    ->color('warning')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                Action::make('preview')
                    ->label('Náhled')
                    ->icon(FilamentIcon::get(AppIcon::VIEW))
                    ->modalHeading('Náhled parsování souboru')
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Zavřít')
                    ->modalContent(fn ($record, LegacyStatExtractor $extractor) => View::make('filament.admin.legacy-import.preview', [
                        'data' => $extractor->extract(Storage::disk('public')->get($record->stored_path), $record->file_type),
                    ])),
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
