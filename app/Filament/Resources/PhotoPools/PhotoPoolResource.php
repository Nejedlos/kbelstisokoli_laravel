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
use App\Support\IconHelper;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
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
use Illuminate\Support\Facades\Blade;
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
                Placeholder::make('ks_global_loader')
                    ->hiddenLabel()
                    ->content(fn () => new HtmlString(Blade::render('
                        <x-loader.basketball wire:target="processImportQueue">
                            <div class="text-center">
                                <strong class="text-lg block mb-1">Hromadné zpracování fotografií...</strong>
                                <span class="text-sm opacity-90">Prosím nezavírejte toto okno a nepřerušujte spojení se serverem.</span>
                            </div>
                        </x-loader.basketball>
                    ')))
                    ->columnSpanFull(),

                Placeholder::make('processing_progress')
                    ->hiddenLabel()
                    ->visible(fn ($record) => $record && (!empty($record->pending_import_queue) || $record->is_processing_import))
                    ->content(function ($record) {
                        $count = count($record->pending_import_queue ?? []);
                        $status = $record->is_processing_import ? 'Probíhá zpracování...' : 'Čeká na zpracování...';
                        $icon = $record->is_processing_import ? 'fa-spinner-third fa-spin' : 'fa-clock';

                        return new HtmlString("
                            <div class='p-6 bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-500 dark:border-amber-600 rounded-2xl flex items-center gap-6 shadow-lg animate-pulse' wire:poll.3s='processImportQueue'>
                                <div class='flex-shrink-0 bg-amber-500 text-white w-14 h-14 rounded-full flex items-center justify-center text-2xl shadow-inner'>
                                    <i class='fa-light {$icon}'></i>
                                </div>
                                <div class='flex-grow'>
                                    <div class='text-lg font-bold text-amber-900 dark:text-amber-100 mb-1'>{$status}</div>
                                    <div class='text-sm text-amber-800 dark:text-amber-200'>
                                        Zbývá zpracovat: <strong>{$count}</strong> fotografií.
                                        <div class='mt-1 font-semibold uppercase tracking-wider text-xs'>Důležité: Prosím nechte toto okno otevřené, dokud import neskončí!</div>
                                    </div>
                                </div>
                                <div wire:loading wire:target='processImportQueue' class='text-amber-600'>
                                    <i class='fa-light fa-arrows-rotate fa-spin text-2xl'></i>
                                </div>
                            </div>
                        ");
                    })
                    ->columnSpanFull(),

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
                                    Select::make('teams')
                                        ->label(__('admin.navigation.resources.team.plural_label'))
                                        ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                                        ->multiple()
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

                                            // SEO Metadata - CS
                                            $set('seo.title.cs', $result['cs']['seo']['title']);
                                            $set('seo.description.cs', $result['cs']['seo']['description']);
                                            $set('seo.keywords.cs', $result['cs']['seo']['keywords']);
                                            $set('seo.og_title.cs', $result['cs']['seo']['og_title']);
                                            $set('seo.og_description.cs', $result['cs']['seo']['og_description']);

                                            // SEO Metadata - EN
                                            $set('seo.title.en', $result['en']['seo']['title']);
                                            $set('seo.description.en', $result['en']['seo']['description']);
                                            $set('seo.keywords.en', $result['en']['seo']['keywords']);
                                            $set('seo.og_title.en', $result['en']['seo']['og_title']);
                                            $set('seo.og_description.en', $result['en']['seo']['og_description']);

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
                                    ->placeholder(CmsForms::getUploadPlaceholder('Klikněte nebo přetáhněte fotografie sem', 'Podporuje hromadný výběr (až 200 souborů najednou)'))
                                    ->multiple()
                                    ->image()
                                    ->reorderable()
                                    ->disk(config('filesystems.uploads.disk'))
                                    ->directory(trim(config('filesystems.uploads.dir', 'uploads'), '/') . '/photo_pools/incoming')
                                    ->getUploadedFileNameForStorageUsing(fn (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string => Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.strtolower($file->getClientOriginalExtension()))
                                    ->downloadable()
                                    ->openable()
                                    ->maxFiles(200)
                                    ->maxSize(30720) // 30 MB
                                    ->maxParallelUploads(10)
                                    ->imageResizeTargetWidth('1920')
                                    ->imageResizeTargetHeight('1920')
                                    ->imageResizeMode('contain')
                                    ->imageResizeUpscale(false)
                                    ->panelLayout('grid')
                                    ->uploadingMessage(__('admin.navigation.resources.photo_pool.notifications.uploading'))
                                    ->extraAttributes([
                                        'style' => 'max-height: 60vh; overflow-y: auto;',
                                        'x-on:file-pond-init' => "console.log('KS DEBUG: FilePond inicializován')",
                                        'x-on:file-pond-add-file' => "console.log('KS DEBUG: Soubor přidán do fronty:', \$event.detail.file.filename)",
                                        'x-on:file-pond-process-file' => "console.log('KS DEBUG: Soubor úspěšně nahrán na server:', \$event.detail.file.filename)",
                                    ])
                                    ->helperText(new HtmlString('<div class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800 text-xs text-blue-700 dark:text-blue-300 flex items-start gap-3">'.IconHelper::render(IconHelper::INFO, 'fal')->toHtml().'<div><strong>Informace k optimalizaci:</strong> Povolené typy: JPG, PNG, WEBP, HEIC. Fotografie jsou pro zvýšení rychlosti automaticky zmenšeny v prohlížeči a následně na serveru převedeny na WebP. Nahrávání probíhá paralelně (10 souborů najednou).</div></div>'))
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'])
                                    ->dehydrated(false)
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
                TextColumn::make('teams.name')
                    ->label(__('admin.navigation.resources.team.plural_label'))
                    ->badge()
                    ->state(fn ($record) => $record->teams->reject(fn($team) => $team->category === 'all')->pluck('name'))
                    ->searchable(),
                TextColumn::make('event_date')
                    ->label('Datum')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return null;
                        $date = \Carbon\Carbon::parse($state);
                        if ($date->day === 1 && $date->month === 1) {
                            return $date->format('Y');
                        }
                        return $date->format('d.m.Y');
                    })
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
                \Filament\Tables\Filters\SelectFilter::make('teams')
                    ->label(__('admin.navigation.resources.team.label'))
                    ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                    ->multiple()
                    ->preload(),
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
