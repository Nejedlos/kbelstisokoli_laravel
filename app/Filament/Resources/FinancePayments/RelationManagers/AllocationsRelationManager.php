<?php

namespace App\Filament\Resources\FinancePayments\RelationManagers;

use App\Services\Finance\FinanceService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AllocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'allocations';

    protected static ?string $title = 'Přiřazeno k předpisům';

    protected static ?string $modelLabel = 'Alokace na předpis';

    protected static ?string $pluralModelLabel = 'Alokace na předpisy';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('finance_charge_id')
                    ->label('Předpis k úhradě')
                    ->relationship('charge', 'title', function ($query) {
                        $payment = $this->getOwnerRecord();
                        $q = $query->whereIn('status', ['open', 'partially_paid', 'overdue']);
                        if ($payment->user_id) {
                            $q->where('user_id', $payment->user_id);
                        }

                        return $q->orderBy('due_date', 'asc');
                    })
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->title} | Zbývá: {$record->amount_remaining} CZK (Splatnost: ".($record->due_date ? $record->due_date->format('d.m.Y') : '-').')')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn ($record) => $record !== null),
                TextInput::make('amount')
                    ->label('Částka k započtení')
                    ->numeric()
                    ->prefix('CZK')
                    ->required()
                    ->default(function () {
                        $payment = $this->getOwnerRecord();

                        return min($payment->amount_available, 0); // Placeholder, v UI se upraví
                    }),
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
                TextColumn::make('charge.user.name')
                    ->label('Člen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('charge.title')
                    ->label('Předpis')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Započtená částka')
                    ->money('CZK')
                    ->sortable(),
                TextColumn::make('allocated_at')
                    ->label('Započteno dne')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Přiřadit k předpisu')
                    ->after(fn ($record) => app(FinanceService::class)->syncChargeStatus($record->charge)),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(fn ($record) => app(FinanceService::class)->syncChargeStatus($record->charge)),
                DeleteAction::make()
                    ->after(fn ($record) => app(FinanceService::class)->syncChargeStatus($record->charge)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(fn ($records) => $records->each(fn ($r) => app(FinanceService::class)->syncChargeStatus($r->charge))),
                ]),
            ]);
    }
}
