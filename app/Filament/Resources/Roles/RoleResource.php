<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\Role;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::ROLES);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.users_and_people');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.role.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.role.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 40;
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
                            ->placeholder('např. super_admin'),
                        TextInput::make('display_name.cs')
                            ->label('Název (CZ)')
                            ->required()
                            ->placeholder('např. Super administrátor'),
                        TextInput::make('display_name.en')
                            ->label('Název (EN)')
                            ->required()
                            ->placeholder('např. Super administrator'),
                        Select::make('permissions')
                            ->label('Oprávnění')
                            ->multiple()
                            ->relationship('permissions', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                            ->preload()
                            ->searchable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_name')
                    ->label('Název role')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Slug')
                    ->description('Systémový název')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('users_count')
                    ->label('Počet uživatelů')
                    ->counts('users')
                    ->sortable(),
                TextColumn::make('permissions.display_name')
                    ->label('Oprávnění')
                    ->badge()
                    ->color('gray')
                    ->separator(', ')
                    ->limitList(3),
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
            'index' => ListRoles::route('/'),
        ];
    }
}
