<?php

namespace App\Filament\Resources\ExternalEntityMappings;

use App\Filament\Resources\ExternalEntityMappings\Pages\CreateExternalEntityMapping;
use App\Filament\Resources\ExternalEntityMappings\Pages\EditExternalEntityMapping;
use App\Filament\Resources\ExternalEntityMappings\Pages\ListExternalEntityMappings;
use App\Filament\Resources\ExternalEntityMappings\Schemas\ExternalEntityMappingForm;
use App\Filament\Resources\ExternalEntityMappings\Tables\ExternalEntityMappingsTable;
use App\Models\ExternalEntityMapping;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExternalEntityMappingResource extends Resource
{
    protected static ?string $model = ExternalEntityMapping::class;

    public static function getNavigationSort(): ?int
    {
        return 33;
    }

    public static function getNavigationLabel(): string
    {
        return 'Párování hráčů';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Párování hráčů';
    }

    public static function getModelLabel(): string
    {
        return 'Párování hráče';
    }

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return FilamentIcon::get(AppIcon::USER_SECRET);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.statistics_and_data') . ' > ' . __('admin.navigation.groups.external_data');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('entity_type', 'player');
    }

    public static function form(Schema $schema): Schema
    {
        return ExternalEntityMappingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExternalEntityMappingsTable::configure($table);
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
            'index' => ListExternalEntityMappings::route('/'),
            'create' => CreateExternalEntityMapping::route('/create'),
            'edit' => EditExternalEntityMapping::route('/{record}/edit'),
        ];
    }
}
