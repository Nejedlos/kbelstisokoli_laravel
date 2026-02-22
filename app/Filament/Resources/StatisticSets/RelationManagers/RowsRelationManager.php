<?php

namespace App\Filament\Resources\StatisticSets\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class RowsRelationManager extends RelationManager
{
    protected static string $relationship = 'rows';

    protected static ?string $title = 'Data (Řádky statistik)';

    protected static ?string $modelLabel = 'Řádek';

    protected static ?string $pluralModelLabel = 'Řádky statistik';

    public function form(Schema $schema): Schema
    {
        $statisticSet = $this->getOwnerRecord();
        $columnConfig = $statisticSet->column_config ?? [];

        $dynamicFields = [];
        foreach ($columnConfig as $column) {
            $key = $column['key'] ?? null;
            $label = $column['label'] ?? $key;
            $type = $column['type'] ?? 'number';

            if ($key) {
                $field = TextInput::make("values.{$key}")
                    ->label($label);

                if ($type === 'number' || $type === 'percentage') {
                    $field->numeric();
                }

                $dynamicFields[] = $field;
            }
        }

        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('player_id')
                            ->label('Hráč')
                            ->relationship('player', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('team_id')
                            ->label('Tým')
                            ->relationship('team', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
                TextInput::make('row_label')
                    ->label('Vlastní štítek')
                    ->helperText('Použijte, pokud není vybrán hráč ani tým.'),
                Section::make('Statistiky (Hodnoty)')
                    ->schema($dynamicFields)
                    ->columns(3),
                Grid::make(2)
                    ->schema([
                        TextInput::make('row_order')
                            ->label('Pořadí')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_visible')
                            ->label('Viditelné')
                            ->default(true),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        $statisticSet = $this->getOwnerRecord();
        $columnConfig = $statisticSet->column_config ?? [];

        $dynamicColumns = [];
        foreach ($columnConfig as $column) {
            $key = $column['key'] ?? null;
            $label = $column['label'] ?? $key;

            if ($key) {
                $dynamicColumns[] = TextColumn::make("values.{$key}")
                    ->label($label)
                    ->sortable($column['sortable'] ?? true);
            }
        }

        return $table
            ->recordTitleAttribute('row_label')
            ->columns(array_merge([
                TextColumn::make('row_order')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('displayName')
                    ->label('Účastník')
                    ->state(fn ($record) => $record->player?->name ?? $record->team?->name ?? $record->row_label)
                    ->searchable(['row_label']),
            ], $dynamicColumns, [
                IconColumn::make('is_visible')
                    ->label('Viditelné')
                    ->boolean(),
            ]))
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
            ->reorderable('row_order');
    }
}
