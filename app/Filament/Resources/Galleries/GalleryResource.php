<?php

namespace App\Filament\Resources\Galleries;

use App\Filament\Resources\Galleries\Pages\CreateGallery;
use App\Filament\Resources\Galleries\Pages\EditGallery;
use App\Filament\Resources\Galleries\Pages\ListGalleries;
use App\Filament\Resources\Galleries\Schemas\GalleryForm;
use App\Filament\Resources\Galleries\Tables\GalleriesTable;
use App\Models\Gallery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GalleryResource extends Resource
{
    protected static ?string $model = Gallery::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.media');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.resources.gallery.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.resources.gallery.plural_label');
    }

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\FilamentIcon::get(\App\Support\FilamentIcon::GALLERIES);
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Galleries\Schemas\GalleryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Galleries\Tables\GalleriesTable::configure($table);
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
