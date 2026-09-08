<?php

namespace App\Filament\Resources\HelpArticles;

use App\Filament\Resources\HelpArticles\Pages\CreateHelpArticle;
use App\Filament\Resources\HelpArticles\Pages\EditHelpArticle;
use App\Filament\Resources\HelpArticles\Pages\ListHelpArticles;
use App\Filament\Resources\HelpArticles\Schemas\HelpArticleForm;
use App\Filament\Resources\HelpArticles\Tables\HelpArticlesTable;
use App\Models\HelpArticle;
use App\Support\IconHelper;
use App\Support\Icons\AppIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class HelpArticleResource extends Resource
{
    protected static ?string $model = HelpArticle::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.system');
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return IconHelper::get(AppIcon::DOCUMENTATION);
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.help_article.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.help_article.plural_label');
    }

    public static function getNavigationSort(): ?int
    {
        return 91;
    }

    public static function form(Schema $schema): Schema
    {
        return HelpArticleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HelpArticlesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FaqsRelationManager::class,
            RelationManagers\QuickActionsRelationManager::class,
            RelationManagers\RelatedArticlesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHelpArticles::route('/'),
            'create' => CreateHelpArticle::route('/create'),
            'edit' => EditHelpArticle::route('/{record}/edit'),
        ];
    }
}
