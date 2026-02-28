<?php

namespace App\Filament\Resources\NotFoundLogs;

use App\Filament\Resources\NotFoundLogs\Pages\ListNotFoundLogs;
use App\Filament\Resources\NotFoundLogs\Schemas\NotFoundLogForm;
use App\Filament\Resources\NotFoundLogs\Tables\NotFoundLogsTable;
use App\Models\NotFoundLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class NotFoundLogResource extends Resource
{
    protected static ?string $model = NotFoundLog::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.admin_tools');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.resources.not_found_log.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::NOT_FOUND);
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.not_found_log.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 95;
    }

    public static function form(Schema $schema): Schema
    {
        return NotFoundLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotFoundLogsTable::configure($table);
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
            'index' => ListNotFoundLogs::route('/'),
        ];
    }
}
