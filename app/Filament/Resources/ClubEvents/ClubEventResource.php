<?php

namespace App\Filament\Resources\ClubEvents;

use App\Filament\Resources\ClubEvents\Pages\CreateClubEvent;
use App\Filament\Resources\ClubEvents\Pages\EditClubEvent;
use App\Filament\Resources\ClubEvents\Pages\ListClubEvents;
use App\Filament\Resources\ClubEvents\Schemas\ClubEventForm;
use App\Filament\Resources\ClubEvents\Tables\ClubEventsTable;
use App\Models\ClubEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Resources\ClubEvents\RelationManagers\AttendancesRelationManager;

class ClubEventResource extends Resource
{
    protected static ?string $model = ClubEvent::class;

    protected static null|string|\UnitEnum $navigationGroup = 'Sportovní agenda';

    protected static ?string $modelLabel = 'Klubová akce';

    protected static ?string $pluralModelLabel = 'Klubové akce';

    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-ticket';

    public static function form(Schema $schema): Schema
    {
        return ClubEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClubEventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AttendancesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClubEvents::route('/'),
            'create' => CreateClubEvent::route('/create'),
            'edit' => EditClubEvent::route('/{record}/edit'),
        ];
    }
}
