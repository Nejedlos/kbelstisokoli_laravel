<?php

namespace App\Filament\Resources\HelpCategories;

use App\Filament\Resources\HelpCategories\Pages\CreateHelpCategory;
use App\Filament\Resources\HelpCategories\Pages\EditHelpCategory;
use App\Filament\Resources\HelpCategories\Pages\ListHelpCategories;
use App\Filament\Resources\HelpCategories\Schemas\HelpCategoryForm;
use App\Filament\Resources\HelpCategories\Tables\HelpCategoriesTable;
use App\Models\HelpCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HelpCategoryResource extends Resource
{
    protected static ?string $model = HelpCategory::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.system');
    }

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return \App\Support\IconHelper::get(\App\Support\Icons\AppIcon::HELP);
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.help_category.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.help_category.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 90;
    }

    public static function form(Schema $schema): Schema
    {
        return HelpCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HelpCategoriesTable::configure($table);
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
            'index' => ListHelpCategories::route('/'),
            'create' => CreateHelpCategory::route('/create'),
            'edit' => EditHelpCategory::route('/{record}/edit'),
        ];
    }
}
