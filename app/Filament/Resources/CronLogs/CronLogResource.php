<?php

namespace App\Filament\Resources\CronLogs;

use App\Filament\Resources\CronLogs\Pages\ListCronLogs;
use App\Filament\Resources\CronLogs\Schemas\CronLogForm;
use App\Filament\Resources\CronLogs\Tables\CronLogsTable;
use App\Models\CronLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CronLogResource extends Resource
{
    protected static ?string $model = CronLog::class;

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.cron_log.plural_label');
    }

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::CRON_LOGS);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.system');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.cron_log.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.cron_log.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 50;
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
