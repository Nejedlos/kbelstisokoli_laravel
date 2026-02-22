<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConsentsRelationManager extends RelationManager
{
    protected static string $relationship = 'consents';

    protected static ?string $title = 'Souhlasy & dokumenty';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('consent_type')
                    ->label('Typ souhlasu')
                    ->options([
                        'gdpr' => 'GDPR',
                        'photos' => 'Fotografie & video',
                        'transport' => 'Doprava / akce',
                        'medical_exam' => 'Lékařská prohlídka',
                    ])
                    ->required(),
                Toggle::make('is_granted')
                    ->label('Udělen')
                    ->default(false),
                DateTimePicker::make('granted_at')
                    ->label('Udělena dne')
                    ->seconds(false),
                Select::make('version')
                    ->label('Verze')
                    ->options([
                        'v1' => 'v1',
                        'v2' => 'v2',
                        'v3' => 'v3',
                    ]),
                Textarea::make('note')
                    ->label('Poznámka')
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('consent_type')
            ->columns([
                TextColumn::make('consent_type')
                    ->label('Typ')
                    ->badge(),
                IconColumn::make('is_granted')
                    ->label('Uděleno')
                    ->boolean(),
                TextColumn::make('granted_at')
                    ->label('Datum')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('version')
                    ->label('Verze')
                    ->badge(),
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
