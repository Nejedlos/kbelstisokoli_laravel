<?php

namespace App\Filament\Resources\CronLogs;

use App\Filament\Resources\CronLogs\Pages\CreateCronLog;
use App\Filament\Resources\CronLogs\Pages\EditCronLog;
use App\Filament\Resources\CronLogs\Pages\ListCronLogs;
use App\Filament\Resources\CronLogs\Schemas\CronLogForm;
use App\Filament\Resources\CronLogs\Tables\CronLogsTable;
use App\Models\CronLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CronLogResource extends Resource
{
    protected static ?string $model = CronLog::class;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.resources.cron_log.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return 'fal_history';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.admin_tools');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.cron_log.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.cron_log.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 81;
    }

    public static function form(Schema $schema): Schema
    {
        return CronLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CronLogsTable::configure($table);
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
            'index' => ListCronLogs::route('/'),
            'view' => Pages\ViewCronLog::route('/{record}'),
        ];
    }
}
