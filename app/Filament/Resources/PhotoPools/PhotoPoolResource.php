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
                            <div class="text-center p-10 bg-white/5 dark:bg-black/40 backdrop-blur-2xl rounded-[3rem] border border-white/20 shadow-[0_32px_64px_-16px_rgba(0,0,0,0.5)] max-w-sm mx-auto overflow-hidden relative">
                                <div class="relative z-10">
                                    <div class="mb-6 inline-flex items-center justify-center w-20 h-20 rounded-[2rem] bg-white/10 text-white shadow-2xl border border-white/20 rotate-6 transition-transform duration-500">
                                        <i class="fa-light fa-arrows-rotate fa-spin text-3xl text-primary-500"></i>
                                    </div>
                                    <strong class="text-3xl font-black block mb-3 text-white tracking-tight uppercase italic leading-none">Hromadný import</strong>
                                    <p class="text-[14px] text-white/70 font-medium leading-relaxed mb-6">
                                        Právě nahráváme a optimalizujeme vaše fotografie. Toto může chvíli trvat v závislosti na počtu souborů.
                                    </p>
                                    <div class="inline-flex items-center gap-3 px-6 py-3 bg-red-600 text-white text-[11px] font-black uppercase tracking-widest rounded-full animate-pulse shadow-xl shadow-red-600/40 border border-red-500/50">
                                        <i class="fa-light fa-shield-exclamation text-base"></i>
                                        Nezavírejte okno
                                    </div>

                                    <button
                                        type="button"
                                        wire:click="cancelImportQueue"
                                        wire:confirm="Opravdu chcete přerušit import a smazat zbývající frontu?"
                                        class="mt-6 flex items-center gap-2 px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white/80 hover:text-white text-[10px] font-bold uppercase tracking-widest rounded-full border border-white/20 transition-all duration-300 mx-auto group"
                                    >
                                        <i class="fa-light fa-circle-xmark text-sm text-red-400 group-hover:scale-110 transition-transform"></i>
                                        Zrušit import
                                    </button>
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

                        return new HtmlString("
                            <div class='relative overflow-hidden p-8 bg-white/40 dark:bg-slate-900/40 backdrop-blur-2xl border border-white/20 dark:border-slate-800/50 rounded-[2.5rem] flex items-center gap-8 shadow-2xl shadow-slate-200/20 dark:shadow-none transition-all duration-500' wire:poll.3s='processImportQueue'>
                                <div class='absolute top-0 right-0 -mt-8 -mr-8 w-32 h-32 bg-primary-500/10 rounded-full blur-3xl'></div>

                                <div class='relative flex-shrink-0'>
                                    <div class='w-20 h-20 rounded-[1.75rem] bg-white dark:bg-slate-800 flex items-center justify-center text-4xl text-primary-600 dark:text-primary-400 shadow-xl border border-white/50 dark:border-slate-700/50'>
                                        <i class='fa-light {$icon} transition-transform duration-700'></i>
                                        <div class='absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 border-4 border-white dark:border-slate-900 rounded-full shadow-lg'></div>
                                    </div>
                                </div>

                                <div class='flex-grow'>
                                    <div class='flex items-center gap-3 mb-2'>
                                        <span class='text-[10px] font-black uppercase tracking-[.25em] text-primary-600 dark:text-primary-400 opacity-80'>Status importu</span>
                                        <div class='h-px flex-grow bg-slate-200 dark:bg-slate-700 opacity-30'></div>
                                    </div>
                                    <div class='text-2xl font-black text-slate-900 dark:text-white leading-tight mb-2 uppercase italic tracking-tight'>{$status}</div>
                                    <div class='text-sm text-slate-600 dark:text-slate-400 font-semibold flex items-center gap-3'>
                                        <span class='px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700'>
                                            Zbývá: <strong class='text-slate-900 dark:text-white'>{$count}</strong> fotek
                                        </span>
                                        <span class='flex items-center gap-2 text-red-600 dark:text-red-400 font-black uppercase text-[11px] tracking-wider animate-pulse'>
                                            <i class='fa-light fa-triangle-exclamation text-base'></i>
                                            Nezavírejte okno!
                                        </span>

                                        <button
                                            type='button'
                                            wire:click='cancelImportQueue'
                                            wire:confirm='Opravdu chcete přerušit import a smazat zbývající frontu?'
                                            class='ml-auto flex items-center gap-2 px-4 py-1.5 bg-red-50 hover:bg-red-100 dark:bg-red-950/30 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 text-[10px] font-black uppercase tracking-widest rounded-xl border border-red-200 dark:border-red-900/50 transition-all duration-300 shadow-sm'
                                        >
                                            <i class='fa-light fa-circle-xmark'></i>
                                            Zrušit import
                                        </button>
                                    </div>
                                </div>

                                <div class='flex-shrink-0' wire:loading wire:target='processImportQueue'>
                                     <div class='flex flex-col items-center gap-2 text-primary-600 dark:text-primary-400 font-black italic text-[10px] uppercase tracking-widest'>
                                        <i class='fa-light fa-basketball fa-spin text-3xl'></i>
                                        <span class='opacity-70'>Zapisuji...</span>
                                     </div>
                                </div>

                                <div class='absolute bottom-0 left-0 h-1.5 bg-slate-100 dark:bg-slate-800 w-full overflow-hidden'>
                                    <div class='h-full bg-primary-600 w-1/3 shadow-[0_0_15px_rgba(var(--color-primary-rgb),0.5)] animate-[ks-progress-bar_2s_infinite_linear]'></div>
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
