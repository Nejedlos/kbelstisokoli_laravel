<?php

namespace App\Filament\Resources\CronTasks;

use App\Filament\Resources\CronTasks\Pages\CreateCronTask;
use App\Filament\Resources\CronTasks\Pages\EditCronTask;
use App\Filament\Resources\CronTasks\Pages\ListCronTasks;
use App\Filament\Resources\CronTasks\Schemas\CronTaskForm;
use App\Filament\Resources\CronTasks\Tables\CronTasksTable;
use App\Models\CronTask;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CronTaskResource extends Resource
{
    protected static ?string $model = CronTask::class;

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-clock';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.settings');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.cron_task.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.cron_task.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return CronTaskForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CronTasksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCronTasks::route('/'),
            'create' => CreateCronTask::route('/create'),
            'edit' => EditCronTask::route('/{record}/edit'),
        ];
    }
}
