<?php

namespace App\Filament\Resources\NotFoundLogs;

use App\Filament\Resources\NotFoundLogs\Pages\CreateNotFoundLog;
use App\Filament\Resources\NotFoundLogs\Pages\EditNotFoundLog;
use App\Filament\Resources\NotFoundLogs\Pages\ListNotFoundLogs;
use App\Filament\Resources\NotFoundLogs\Schemas\NotFoundLogForm;
use App\Filament\Resources\NotFoundLogs\Tables\NotFoundLogsTable;
use App\Models\NotFoundLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NotFoundLogResource extends Resource
{
    protected static ?string $model = NotFoundLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
            'create' => CreateNotFoundLog::route('/create'),
            'edit' => EditNotFoundLog::route('/{record}/edit'),
        ];
    }
}
