<?php

namespace App\Filament\Resources\PhotoPools;

use App\Filament\Resources\PhotoPools\Pages\CreatePhotoPool;
use App\Filament\Resources\PhotoPools\Pages\EditPhotoPool;
use App\Filament\Resources\PhotoPools\Pages\ListPhotoPools;
use App\Models\PhotoPool;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PhotoPoolResource extends Resource
{
    protected static ?string $model = PhotoPool::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.media');
    }

    public static function getModelLabel(): string
    {
        return 'Pool fotografií';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pooly fotografií';
    }

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::PHOTO_FILM);
    }

    public static function getNavigationSort(): ?int
    {
        return 0;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Informace o akci')->schema([
                TextInput::make('title')
                    ->label('Název akce')
                    ->required()
                    ->maxLength(200),
                TextInput::make('slug')
                    ->label('Slug')
                    ->helperText('Pokud ponecháte prázdné, vygeneruje se automaticky z názvu.')
                    ->maxLength(200),
                Select::make('event_type')
                    ->label('Typ akce')
                    ->options([
                        'tournament' => 'Turnaj',
                        'match' => 'Zápas',
                        'training' => 'Trénink',
                        'club_event' => 'Klubová akce',
                        'other' => 'Jiné',
                    ])->native(false),
                DatePicker::make('event_date')
                    ->label('Datum akce')
                    ->native(false),
                Textarea::make('description')
                    ->label('Popis akce')
                    ->rows(5),
            ])->columns(2),
            Section::make('Viditelnost')->schema([
                Toggle::make('is_public')->label('Veřejné')->default(true),
                Toggle::make('is_visible')->label('Viditelné')->default(true),
            ])->columns(2),
            Section::make('Fotografie (hromadné nahrání)')->schema([
                \Filament\Forms\Components\FileUpload::make('photos')
                    ->label('Soubory')
                    ->multiple()
                    ->image()
                    ->directory('uploads/photos')
                    ->preserveFilenames()
                    ->downloadable()
                    ->openable()
                    ->maxFiles(200)
                    ->minSize(1) // KB
                    ->maxSize(25600) // ~25 MB
                    ->imageEditor()
                    ->helperText('Povolené typy: JPG, PNG, WEBP, HEIC/HEIF. Velké fotky budou normalizované.')
                    ->acceptedFileTypes(['image/jpeg','image/png','image/webp','image/heic','image/heif'])
                    ->dehydrate(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Název')->searchable()->limit(50),
                BadgeColumn::make('event_type')->label('Typ')->colors([
                    'primary' => 'tournament',
                    'success' => 'match',
                    'warning' => 'training',
                    'info' => 'club_event',
                    'gray' => 'other',
                ])->sortable(),
                TextColumn::make('event_date')->label('Datum')->date()->sortable(),
                IconColumn::make('is_public')->label('Veřejné')->boolean(),
                IconColumn::make('is_visible')->label('Viditelné')->boolean(),
                TextColumn::make('mediaAssets_count')->counts('mediaAssets')->label('Fotek')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // V budoucnu: RelationManager na média přímo v poolu
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPhotoPools::route('/'),
            'create' => CreatePhotoPool::route('/create'),
            'edit' => EditPhotoPool::route('/{record}/edit'),
        ];
    }
}
