<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Enums\CommunicationChannel;
use App\Enums\RelationshipType;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ParentsRelationManager extends RelationManager
{
    protected static string $relationship = 'parents';

    protected static ?string $title = 'Rodiče / Zákonní zástupci';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('relationship_type')
                    ->label('Vztah')
                    ->options(RelationshipType::class)
                    ->required(),
                Toggle::make('is_emergency_contact')
                    ->label('Nouzový kontakt'),
                Toggle::make('is_billing_contact')
                    ->label('Fakturační kontakt'),
                Select::make('preferred_communication_channel')
                    ->label('Preferovaný kanál')
                    ->options(CommunicationChannel::class),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Jméno')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('pivot.relationship_type')
                    ->label('Vztah')
                    ->badge(),
                IconColumn::make('pivot.is_emergency_contact')
                    ->label('Nouzový')
                    ->boolean(),
                IconColumn::make('pivot.is_billing_contact')
                    ->label('Fakturace')
                    ->boolean(),
                TextColumn::make('pivot.preferred_communication_channel')
                    ->label('Kanál')
                    ->badge(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name','email'])
                    ->form([
                        Select::make('relationship_type')
                            ->label('Vztah')
                            ->options(RelationshipType::class)
                            ->required(),
                        Toggle::make('is_emergency_contact')
                            ->label('Nouzový kontakt'),
                        Toggle::make('is_billing_contact')
                            ->label('Fakturační kontakt'),
                        Select::make('preferred_communication_channel')
                            ->label('Preferovaný kanál')
                            ->options(CommunicationChannel::class),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
