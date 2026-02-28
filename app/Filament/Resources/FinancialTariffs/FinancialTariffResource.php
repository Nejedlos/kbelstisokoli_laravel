<?php

namespace App\Filament\Resources\FinancialTariffs;

use App\Filament\Resources\FinancialTariffs\Pages\CreateFinancialTariff;
use App\Filament\Resources\FinancialTariffs\Pages\EditFinancialTariff;
use App\Filament\Resources\FinancialTariffs\Pages\ListFinancialTariffs;
use App\Models\FinancialTariff;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class FinancialTariffResource extends Resource
{
    protected static ?string $model = FinancialTariff::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.finance');
    }

    public static function getModelLabel(): string
    {
        return 'Finanční tarif';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Finanční tarify';
    }

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::BANKNOTES);
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\FinancialTariffs\Schemas\FinancialTariffForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\FinancialTariffs\Tables\FinancialTariffsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinancialTariffs::route('/'),
            'create' => CreateFinancialTariff::route('/create'),
            'edit' => EditFinancialTariff::route('/{record}/edit'),
        ];
    }
}
