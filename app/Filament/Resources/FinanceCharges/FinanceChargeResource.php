<?php

namespace App\Filament\Resources\FinanceCharges;

use App\Filament\Resources\FinanceCharges\Pages\CreateFinanceCharge;
use App\Filament\Resources\FinanceCharges\Pages\EditFinanceCharge;
use App\Filament\Resources\FinanceCharges\Pages\ListFinanceCharges;
use App\Filament\Resources\FinanceCharges\Schemas\FinanceChargeForm;
use App\Filament\Resources\FinanceCharges\Tables\FinanceChargesTable;
use App\Models\FinanceCharge;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FinanceChargeResource extends Resource
{
    protected static ?string $model = FinanceCharge::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return FinanceChargeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinanceChargesTable::configure($table);
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
            'index' => ListFinanceCharges::route('/'),
            'create' => CreateFinanceCharge::route('/create'),
            'edit' => EditFinanceCharge::route('/{record}/edit'),
        ];
    }
}
