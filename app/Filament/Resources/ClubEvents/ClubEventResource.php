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

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.sports_agenda');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.club_event.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.club_event.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::EVENTS);
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

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
