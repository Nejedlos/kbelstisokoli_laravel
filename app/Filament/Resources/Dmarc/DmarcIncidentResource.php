<?php

namespace App\Filament\Resources\Dmarc;

use App\Filament\Resources\Dmarc\DmarcIncidentResource\Pages;
use App\Filament\Resources\Dmarc\DmarcIncidentResource\Schemas\IncidentForm;
use App\Filament\Resources\Dmarc\DmarcIncidentResource\Tables\IncidentsTable;
use App\Models\Dmarc\DmarcIncident;
use App\Support\IconHelper;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DmarcIncidentResource extends Resource
{
    protected static ?string $model = DmarcIncident::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.dmarc_monitor');
    }

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return IconHelper::get(IconHelper::EMERGENCY);
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.dmarc_incident.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.dmarc_incident.plural_label');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('state', 'open')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return IncidentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IncidentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncidents::route('/'),
            'edit' => Pages\EditIncident::route('/{record}/edit'),
        ];
    }
}
