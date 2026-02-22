<?php

namespace App\Filament\Resources\Trainings;

use App\Filament\Resources\Trainings\Pages\CreateTraining;
use App\Filament\Resources\Trainings\Pages\EditTraining;
use App\Filament\Resources\Trainings\Pages\ListTrainings;
use App\Filament\Resources\Trainings\Schemas\TrainingForm;
use App\Filament\Resources\Trainings\Tables\TrainingsTable;
use App\Models\Training;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Resources\ClubEvents\RelationManagers\AttendancesRelationManager;

class TrainingResource extends Resource
{
    protected static ?string $model = Training::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.sports_agenda');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.training.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.training.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\FilamentIcon::get(\App\Support\FilamentIcon::TRAININGS);
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return TrainingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingsTable::configure($table);
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
            'index' => ListTrainings::route('/'),
            'create' => CreateTraining::route('/create'),
            'edit' => EditTraining::route('/{record}/edit'),
        ];
    }
}
