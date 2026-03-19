<?php

namespace App\Filament\Resources\Permissions;

use App\Filament\Resources\Permissions\Pages\ListPermissions;
use App\Models\Permission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::PERMISSIONS);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.users_and_people');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.permission.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.permission.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 50;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Základní informace')
                    ->schema([
                        TextInput::make('name')
                            ->label('Slug (systémový název)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('např. manage_settings'),
                        TextInput::make('display_name.cs')
                            ->label('Název (CZ)')
                            ->required()
                            ->placeholder('např. Správa nastavení'),
                        TextInput::make('display_name.en')
                            ->label('Název (EN)')
                            ->required()
                            ->placeholder('např. Manage settings'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_name')
                    ->label('Název oprávnění')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Slug')
                    ->description('Systémový název')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('roles.display_name')
                    ->label('Přiřazeno rolím')
                    ->badge()
                    ->color('info')
                    ->separator(', '),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
        ];
    }
}
