<?php

namespace App\Filament\Resources\Galleries;

use App\Filament\Resources\Galleries\Pages\CreateGallery;
use App\Filament\Resources\Galleries\Pages\EditGallery;
use App\Filament\Resources\Galleries\Pages\ListGalleries;
use App\Filament\Resources\Galleries\Schemas\GalleryForm;
use App\Filament\Resources\Galleries\Tables\GalleriesTable;
use App\Models\Gallery;
use App\Support\IconHelper;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.web_settings');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.gallery.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.gallery.plural_label');
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return IconHelper::get(IconHelper::GALLERIES);
    }

    public static function getNavigationSort(): ?int
    {
        return 30;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return []; // Vypnuto kvůli translatable polím a kompatibilitě s Webglobe (json_unquote)
    }

    public static function form(Schema $schema): Schema
    {
        return GalleryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GalleriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MediaAssetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleries::route('/'),
            'create' => CreateGallery::route('/create'),
            'edit' => EditGallery::route('/{record}/edit'),
        ];
    }
}
