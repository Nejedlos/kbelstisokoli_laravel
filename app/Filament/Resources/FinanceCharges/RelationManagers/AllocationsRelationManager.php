<?php

namespace App\Filament\Resources\FinanceCharges\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use App\Services\Finance\FinanceService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;

class AllocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'allocations';

    protected static ?string $title = 'Přiřazené platby';

    protected static ?string $modelLabel = 'Alokace platby';

    protected static ?string $pluralModelLabel = 'Alokace plateb';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('finance_payment_id')
                    ->label('Platba')
                    ->relationship('payment', 'id', function ($query) {
                        $charge = $this->getOwnerRecord();
                        return $query->where('user_id', $charge->user_id)
                                     ->orWhereNull('user_id')
                                     ->orderBy('paid_at', 'desc');
                    })
                    ->getOptionLabelFromRecordUsing(fn ($record) => "VS: {$record->variable_symbol} | {$record->amount} CZK ({$record->paid_at->format('d.m.Y')})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn ($record) => $record !== null),
                TextInput::make('amount')
                    ->label('Částka k započtení')
                    ->numeric()
                    ->prefix('CZK')
                    ->required()
                    ->default(fn () => $this->getOwnerRecord()->amount_remaining),
                DateTimePicker::make('allocated_at')
                    ->label('Datum započtení')
                    ->native(false)
                    ->default(now())
                    ->required(),
                Textarea::make('note')
                    ->label('Poznámka')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('payment.paid_at')
                    ->label('Datum platby')
                    ->dateTime('d.m.Y')
                    ->sortable(),
                TextColumn::make('payment.variable_symbol')
                    ->label('VS')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Započtená částka')
                    ->money('CZK')
                    ->sortable(),
                TextColumn::make('allocated_at')
                    ->label('Započteno dne')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Přiřadit platbu')
                    ->after(fn () => app(FinanceService::class)->syncChargeStatus($this->getOwnerRecord())),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(fn () => app(FinanceService::class)->syncChargeStatus($this->getOwnerRecord())),
                DeleteAction::make()
                    ->after(fn () => app(FinanceService::class)->syncChargeStatus($this->getOwnerRecord())),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(fn () => app(FinanceService::class)->syncChargeStatus($this->getOwnerRecord())),
                ]),
            ]);
    }
}
