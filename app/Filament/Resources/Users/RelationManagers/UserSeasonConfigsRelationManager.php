<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\UserSeasonConfigs\Schemas\UserSeasonConfigForm;
use App\Filament\Resources\UserSeasonConfigs\Tables\UserSeasonConfigsTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UserSeasonConfigsRelationManager extends RelationManager
{
    protected static string $relationship = 'userSeasonConfigs';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'Historie plátce (Sezónní konfigurace)';
    }

    public function form(Schema $schema): Schema
    {
        return UserSeasonConfigForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return UserSeasonConfigsTable::configure($table)
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
