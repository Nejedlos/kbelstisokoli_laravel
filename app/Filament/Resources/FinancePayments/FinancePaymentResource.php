<?php

namespace App\Filament\Resources\FinancePayments;

use App\Filament\Resources\FinancePayments\Pages\CreateFinancePayment;
use App\Filament\Resources\FinancePayments\Pages\EditFinancePayment;
use App\Filament\Resources\FinancePayments\Pages\ListFinancePayments;
use App\Filament\Resources\FinancePayments\Schemas\FinancePaymentForm;
use App\Filament\Resources\FinancePayments\Tables\FinancePaymentsTable;
use App\Models\FinancePayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FinancePaymentResource extends Resource
{
    protected static ?string $model = FinancePayment::class;

    public static function getNavigationIcon(): ?string
    {
        return 'fal_money_bill_transfer';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.finance');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.finance_payment.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.finance_payment.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return FinancePaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancePaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AllocationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinancePayments::route('/'),
            'create' => CreateFinancePayment::route('/create'),
            'edit' => EditFinancePayment::route('/{record}/edit'),
        ];
    }
}
