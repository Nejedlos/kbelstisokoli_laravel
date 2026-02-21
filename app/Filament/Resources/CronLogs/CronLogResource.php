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

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-list-bullet';

    protected static null|string|\UnitEnum $navigationGroup = 'Nastavení';

    protected static ?string $modelLabel = 'Log plánované úlohy';

    protected static ?string $pluralModelLabel = 'Logy cronu';

    protected static ?string $navigationLabel = 'Cron logy';

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
