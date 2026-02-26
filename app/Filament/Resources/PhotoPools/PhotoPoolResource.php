<?php

namespace App\Filament\Resources\PhotoPools;

use App\Filament\Forms\CmsForms;
use App\Filament\Resources\PhotoPools\Pages\CreatePhotoPool;
use App\Filament\Resources\PhotoPools\Pages\EditPhotoPool;
use App\Filament\Resources\PhotoPools\Pages\ListPhotoPools;
use App\Models\PhotoPool;
use App\Services\AiTextEnhancer;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

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
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('PhotoPoolTabs')
                    ->tabs([
                        Tabs\Tab::make('Metadata')
                            ->label(new HtmlString('<i class="fa-light fa-info-circle mr-1"></i> Základní informace'))
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('slug')
                                        ->label('Slug / URL indentifikátor')
                                        ->required()
                                        ->unique(PhotoPool::class, 'slug', ignoreRecord: true)
                                        ->maxLength(200),
                                    Select::make('event_type')
                                        ->label('Typ akce')
                                        ->options([
                                            'tournament' => 'Turnaj',
                                            'match' => 'Zápas',
                                            'training' => 'Trénink',
                                            'club_event' => 'Klubová akce',
                                            'other' => 'Jiné',
                                        ])
                                        ->native(false)
                                        ->required(),
                                    DatePicker::make('event_date')
                                        ->label('Datum akce')
                                        ->native(false)
                                        ->required(),
                                    Select::make('team_id')
                                        ->label('Tým')
                                        ->relationship('team', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->native(false),
                                ]),
                                Actions::make([
                                    Action::make('regenerateAi')
                                        ->label(__('admin.navigation.resources.photo_pool.actions.regenerate_ai'))
                                        ->icon(new HtmlString('<i class="fa-light fa-wand-magic-sparkles"></i>'))
                                        ->color('info')
                                        ->action(function ($get, $set, AiTextEnhancer $enhancer) {
                                            $result = $enhancer->suggestPhotoPoolMetadataBilingual(
                                                $get('title.cs') ?? '',
                                                $get('event_date'),
                                                $get('description.cs') ?? ''
                                            );

                                            $set('title.cs', $result['cs']['title']);
                                            $set('title.en', $result['en']['title']);
                                            $set('description.cs', $result['cs']['description']);
                                            $set('description.en', $result['en']['description']);
                                            $set('event_date', $result['date']);
                                            $set('slug', $result['slug']);

                                            \Filament\Notifications\Notification::make()
                                                ->title(__('admin.navigation.resources.photo_pool.notifications.ai_regenerated'))
                                                ->success()
                                                ->send();
                                        }),
                                ])->columnSpanFull(),
                                Tabs::make('Translations')
                                    ->tabs([
                                        Tabs\Tab::make('Čeština')
                                            ->icon(new HtmlString('<i class="fa-light fa-flag-checkered"></i>'))
                                            ->schema([
                                                TextInput::make('title.cs')
                                                    ->label('Název akce (cs)')
                                                    ->required()
                                                    ->maxLength(200)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($get, $set, ?string $state) {
                                                        if (! $get('slug') && $state) {
                                                            $set('slug', Str::slug($state));
                                                        }
                                                    }),
                                                Textarea::make('description.cs')
                                                    ->label('Popis akce (cs)')
                                                    ->rows(6),
                                            ]),
                                        Tabs\Tab::make('English')
                                            ->icon(new HtmlString('<i class="fa-light fa-flag-usa"></i>'))
                                            ->schema([
                                                TextInput::make('title.en')
                                                    ->label('Event name (en)')
                                                    ->required()
                                                    ->maxLength(200),
                                                Textarea::make('description.en')
                                                    ->label('Event description (en)')
                                                    ->rows(6),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('Photos')
                            ->label(new HtmlString('<i class="fa-light fa-images mr-1"></i> Fotografie'))
                            ->schema([
                                FileUpload::make('photos')
                                    ->label('Hromadné nahrávání fotografií')
                                    ->multiple()
                                    ->image()
                                    ->reorderable()
                                    ->disk(config('filesystems.uploads.disk'))
                                    ->directory('uploads/photos/pools')
                                    ->getUploadedFileNameForStorageUsing(fn (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string => Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.strtolower($file->getClientOriginalExtension()))
                                    ->downloadable()
                                    ->openable()
                                    ->maxFiles(200)
                                    ->maxSize(30720) // 30 MB
                                    ->imageEditor()
                                    ->helperText('Povolené typy: JPG, PNG, WEBP, HEIC. Fotografie budou automaticky optimalizovány pro web (WebP).')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'])
                                    ->dehydrated(false) // Zpracováváme v Create/Edit stránce
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make('Settings')
                            ->label(new HtmlString('<i class="fa-light fa-gear mr-1"></i> Nastavení'))
                            ->schema([
                                Grid::make(2)->schema([
                                    Toggle::make('is_public')
                                        ->label('Veřejně dostupné')
                                        ->helperText('Pokud je vypnuto, pool uvidí pouze administrátoři.')
                                        ->default(true),
                                    Toggle::make('is_visible')
                                        ->label('Aktivní / Viditelné')
                                        ->helperText('Umožňuje dočasně skrýt pool z výběru v galeriích.')
                                        ->default(true),
                                ]),
                            ]),

                        Tabs\Tab::make('SEO')
                            ->label(new HtmlString('<i class="fa-light fa-globe mr-1"></i> SEO'))
                            ->schema([
                                CmsForms::getSeoSection(),
                            ]),
                    ])
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Název akce')
                    ->formatStateUsing(fn ($state, PhotoPool $record) => (string) $record->getTranslation('title', app()->getLocale()))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('event_type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tournament' => 'Turnaj',
                        'match' => 'Zápas',
                        'training' => 'Trénink',
                        'club_event' => 'Akce',
                        default => 'Jiné',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'tournament' => 'danger',
                        'match' => 'success',
                        'training' => 'warning',
                        'club_event' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('event_date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
                IconColumn::make('is_public')
                    ->label('Veř.')
                    ->boolean()
                    ->tooltip('Veřejně dostupné'),
                IconColumn::make('is_visible')
                    ->label('Vid.')
                    ->boolean()
                    ->tooltip('Viditelné v nabídce'),
                TextColumn::make('media_assets_count')
                    ->counts('mediaAssets')
                    ->label('Počet fotek')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
            ])
            ->defaultSort('event_date', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('event_type')
                    ->label('Typ akce')
                    ->options([
                        'tournament' => 'Turnaj',
                        'match' => 'Zápas',
                        'training' => 'Trénink',
                        'club_event' => 'Klubová akce',
                        'other' => 'Jiné',
                    ]),
            ])
            ->actions([
                EditAction::make()->icon(new HtmlString('<i class="fa-light fa-pen-to-square"></i>')),
                DeleteAction::make()->icon(new HtmlString('<i class="fa-light fa-trash"></i>')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\PhotoPools\RelationManagers\MediaAssetsRelationManager::class,
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
