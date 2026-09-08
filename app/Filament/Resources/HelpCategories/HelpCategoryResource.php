<?php

namespace App\Filament\Resources\HelpCategories;

use App\Filament\Resources\HelpCategories\Pages\CreateHelpCategory;
use App\Filament\Resources\HelpCategories\Pages\EditHelpCategory;
use App\Filament\Resources\HelpCategories\Pages\ListHelpCategories;
use App\Filament\Resources\HelpCategories\Schemas\HelpCategoryForm;
use App\Filament\Resources\HelpCategories\Tables\HelpCategoriesTable;
use App\Models\HelpCategory;
use App\Support\IconHelper;
use App\Support\Icons\AppIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class HelpCategoryResource extends Resource
{
    protected static ?string $model = HelpCategory::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.system');
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return IconHelper::get(AppIcon::HELP);
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
