<?php

namespace App\Filament\Resources\ExternalImportRuns\RelationManagers;

use App\Support\FilamentIcon;
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('action')
                            ->readOnly(),
                        TextInput::make('model_type')
                            ->readOnly(),
                        TextInput::make('model_id')
                            ->readOnly(),
                        Textarea::make('message')
                            ->columnSpanFull()
                            ->readOnly(),
                        KeyValue::make('old_values')
                            ->columnSpanFull()
                            ->disableAddingRows()
                            ->disableDeletingRows()
                            ->disableEditingKeys()
                            ->disableEditingValues(),
                        KeyValue::make('new_values')
                            ->columnSpanFull()
                            ->disableAddingRows()
                            ->disableDeletingRows()
                            ->disableEditingKeys()
                            ->disableEditingValues(),
                    ])->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('action')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('Log čas'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('action')
                    ->label(__('Akce'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'skipped' => 'gray',
                        'error' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('model_type')
                    ->label(__('Model'))
                    ->formatStateUsing(fn (string $state) => class_basename($state))
                    ->description(fn ($record) => "ID: {$record->model_id}"),
                TextColumn::make('message')
                    ->label(__('Zpráva'))
                    ->wrap()
                    ->searchable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
                ViewAction::make()
                    ->icon(new HtmlString('<i class="fa-light fa-eye"></i>')),
                DeleteAction::make()
                    ->icon(new HtmlString('<i class="fa-light fa-trash"></i>')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
