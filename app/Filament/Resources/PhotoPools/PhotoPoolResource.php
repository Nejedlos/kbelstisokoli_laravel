<?php

namespace App\Filament\Resources\PhotoPools;

use App\Filament\Resources\PhotoPools\Pages\CreatePhotoPool;
use App\Filament\Resources\PhotoPools\Pages\EditPhotoPool;
use App\Filament\Resources\PhotoPools\Pages\ListPhotoPools;
use App\Models\PhotoPool;
use App\Services\AiTextEnhancer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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
                                    TextInput::make('title')
                                        ->label('Název akce')
                                        ->required()
                                        ->maxLength(200)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                            if (!$get('slug') && $state) {
                                                $set('slug', Str::slug($state));
                                            }
                                        })
                                        ->hintAction(
                                            \Filament\Forms\Components\Actions\Action::make('suggestAi')
                                                ->label('Vylepšit pomocí AI')
                                                ->icon('heroicon-m-sparkles')
                                                ->color('primary')
                                                ->action(function (Get $get, Set $set, AiTextEnhancer $enhancer) {
                                                    $locale = app()->getLocale();
                                                    $result = $enhancer->suggestPhotoPoolMetadata(
                                                        (string)($get('title') ?? ''),
                                                        $get('event_date') ?? null,
                                                        (string)($get('description') ?? ''),
                                                        $locale,
                                                    );

                                                    $set('title', $result['title']);
                                                    if ($result['date']) {
                                                        $set('event_date', $result['date']);
                                                    }
                                                    $set('description', $result['description']);

                                                    // Update slug if it was empty
                                                    if (!$get('slug')) {
                                                        $set('slug', Str::slug($result['title']));
                                                    }
                                                })
                                        ),
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
                                ]),
                                Textarea::make('description')
                                    ->label('Popis akce (vylepšeno AI)')
                                    ->placeholder('Zadejte stručné informace a klikněte na "Vylepšit pomocí AI" výše...')
                                    ->rows(6)
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make('Photos')
                            ->label(new HtmlString('<i class="fa-light fa-images mr-1"></i> Fotografie'))
                            ->schema([
                                FileUpload::make('photos')
                                    ->label('Hromadné nahrávání fotografií')
                                    ->multiple()
                                    ->image()
                                    ->reorderable()
                                    ->directory('uploads/photos/pools')
                                    ->preserveFilenames()
                                    ->downloadable()
                                    ->openable()
                                    ->maxFiles(200)
                                    ->maxSize(30720) // 30 MB
                                    ->imageEditor()
                                    ->helperText('Povolené typy: JPG, PNG, WEBP, HEIC. Fotografie budou automaticky optimalizovány pro web (WebP).')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'])
                                    ->dehydrate(false) // Zpracováváme v Create/Edit stránce
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
                \Filament\Tables\Actions\DeleteAction::make()->icon(new HtmlString('<i class="fa-light fa-trash"></i>')),
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
