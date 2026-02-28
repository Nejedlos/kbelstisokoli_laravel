<?php

namespace App\Filament\Resources\PhotoPools;

use App\Filament\Forms\CmsForms;
use App\Filament\Resources\PhotoPools\Pages\CreatePhotoPool;
use App\Filament\Resources\PhotoPools\Pages\EditPhotoPool;
use App\Filament\Resources\PhotoPools\Pages\ListPhotoPools;
use App\Models\PhotoPool;
use App\Services\AiTextEnhancer;
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
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
                            <div class="text-center p-8 bg-white/10 dark:bg-black/20 backdrop-blur-xl rounded-[2.5rem] border border-white/20 shadow-2xl max-w-sm mx-auto overflow-hidden relative">
                                <!-- Sokolský brand pattern na pozadí (přes pseudo-element by to bylo složitější, tak aspoň SVG blob) -->
                                <div class="absolute -top-10 -right-10 w-32 h-32 bg-primary-500/10 rounded-full blur-2xl"></div>
                                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-red-500/10 rounded-full blur-2xl"></div>

                                <div class="relative z-10">
                                    <div class="mb-5 inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 text-white shadow-xl rotate-3 border border-white/20">
                                        <i class="fa-light fa-arrows-rotate fa-spin text-2xl"></i>
                                    </div>
                                    <strong class="text-2xl font-black block mb-2 text-white tracking-tight uppercase italic leading-none">Hromadný import</strong>
                                    <p class="text-[13px] text-white/80 font-medium leading-relaxed mb-4">
                                        Právě nahráváme a optimalizujeme vaše fotografie. Toto může chvíli trvat v závislosti na počtu souborů.
                                    </p>
                                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-[10px] font-black uppercase tracking-widest rounded-full animate-pulse shadow-lg shadow-red-600/30">
                                        <i class="fa-light fa-triangle-exclamation"></i>
                                        Nezavírejte okno
                                    </div>
                                </div>
                            </div>
                        </x-loader.basketball>
                    ')))
                    ->columnSpanFull(),

                Placeholder::make('processing_progress')
                    ->hiddenLabel()
                    ->visible(fn ($record) => $record && (! empty($record->pending_import_queue) || $record->is_processing_import))
                    ->content(function ($record) {
                        $count = count($record->pending_import_queue ?? []);
                        $status = $record->is_processing_import ? 'Probíhá import fotografií' : 'Čeká na zpracování';
                        $icon = $record->is_processing_import ? 'fa-spinner-third fa-spin' : 'fa-clock';

                        // Sokolské barvy z CSS proměnných nebo Tailwindu
                        // Brand Navy: #001F3F (přibližně slate-900)
                        // Brand Red: #E41F18

                        return new HtmlString("
                            <div class='relative overflow-hidden p-6 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-slate-200 dark:border-slate-800 rounded-3xl flex items-center gap-6 shadow-xl shadow-slate-200/50 dark:shadow-none transition-all duration-500' wire:poll.3s='processImportQueue'>
                                <div class='absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-primary-500/5 rounded-full blur-3xl'></div>

                                <div class='relative flex-shrink-0'>
                                    <div class='w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-3xl text-primary-600 dark:text-primary-400 shadow-inner overflow-hidden'>
                                        <i class='fa-light {$icon} transition-transform duration-700'></i>
                                        <div class='absolute bottom-0 right-0 w-4 h-4 bg-green-500 border-2 border-white dark:border-slate-800 rounded-full'></div>
                                    </div>
                                </div>

                                <div class='flex-grow'>
                                    <div class='flex items-center gap-2 mb-1'>
                                        <span class='text-xs font-black uppercase tracking-widest text-primary-600 dark:text-primary-400'>Status</span>
                                        <div class='h-px flex-grow bg-slate-100 dark:bg-slate-800'></div>
                                    </div>
                                    <div class='text-xl font-black text-slate-900 dark:text-white leading-tight mb-1'>{$status}</div>
                                    <div class='text-sm text-slate-500 dark:text-slate-400 font-medium flex items-center gap-2'>
                                        <span>Zbývá zpracovat: <strong class='text-slate-900 dark:text-white'>{$count}</strong> fotografií</span>
                                        <span class='w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-600'></span>
                                        <span class='text-red-600 dark:text-red-400 font-bold animate-pulse'>Nezavírejte toto okno!</span>
                                    </div>
                                </div>

                                <div class='flex-shrink-0' wire:loading wire:target='processImportQueue'>
                                     <div class='flex flex-col items-center gap-1 text-primary-600 dark:text-primary-400 font-black italic text-[10px] uppercase tracking-tighter'>
                                        <i class='fa-light fa-basketball fa-spin text-2xl'></i>
                                        <span>Zápis...</span>
                                     </div>
                                </div>

                                <div class='absolute bottom-0 left-0 h-1 bg-primary-500/20 w-full overflow-hidden'>
                                    <div class='h-full bg-primary-50 w-1/3 animate-[ks-progress-bar_2s_infinite_linear]'></div>
                                </div>

                                <style>
                                    @keyframes ks-progress-bar {
                                        0% { transform: translateX(-100%); }
                                        100% { transform: translateX(300%); }
                                    }
                                </style>
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
                                    ->directory(trim(config('filesystems.uploads.dir', 'uploads'), '/').'/photo_pools/incoming')
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
                    ->state(fn ($record) => $record->teams->reject(fn ($team) => $team->category === 'all')->pluck('name'))
                    ->searchable(),
                TextColumn::make('event_date')
                    ->label('Datum')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return null;
                        }
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
