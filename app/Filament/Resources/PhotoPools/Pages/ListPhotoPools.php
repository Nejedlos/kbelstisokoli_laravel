<?php

namespace App\Filament\Resources\PhotoPools\Pages;

use App\Filament\Forms\CmsForms;
use App\Filament\Resources\PhotoPools\PhotoPoolResource;
use App\Models\MediaAsset;
use App\Models\PhotoPool;
use App\Services\AiTextEnhancer;
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ListPhotoPools extends ListRecords
{
    protected static string $resource = PhotoPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('admin.navigation.resources.photo_pool.actions.create_wizard'))
                ->icon(new HtmlString('<i class="fa-light fa-plus"></i>'))
                ->modalWidth('4xl')
                ->form([
                    Placeholder::make('ks_global_loader')
                        ->label('')
                        ->content(fn () => new HtmlString(Blade::render('<x-loader.basketball id="ks-basketball-loader" style="display: none;" />')))
                        ->columnSpanFull(),

                    Placeholder::make('processing_progress')
                        ->label('')
                        ->content(fn () => new HtmlString('<div wire:stream="ks-loader-progress-text" class="text-sm font-bold text-primary-600 dark:text-primary-400 text-center animate-pulse"></div>'))
                        ->columnSpanFull(),

                    Wizard::make([
                        Step::make('Kontext akce')
                        ->label(__('admin.navigation.resources.photo_pool.steps.context.label'))
                        ->description(__('admin.navigation.resources.photo_pool.steps.context.description'))
                        ->icon(new HtmlString('<i class="fa-light fa-sparkles"></i>'))
                        ->schema([
                            TextInput::make('preliminary_title')
                                ->label(__('admin.navigation.resources.photo_pool.fields.preliminary_title'))
                                ->placeholder('Např. Turnaj v Kbelích')
                                ->required(),
                            DatePicker::make('preliminary_date')
                                ->label(__('admin.navigation.resources.photo_pool.fields.preliminary_date'))
                                ->native(false)
                                ->displayFormat('m/Y')
                                ->required(),
                            Select::make('teams')
                                ->label(__('admin.navigation.resources.team.plural_label'))
                                ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->native(false),
                            Textarea::make('preliminary_description')
                                ->label(__('admin.navigation.resources.photo_pool.fields.preliminary_description'))
                                ->placeholder('O co šlo, kdo tam byl...')
                                ->rows(4)
                                ->required(),
                        ])
                        ->afterValidation(function (array $state, $set, AiTextEnhancer $enhancer) {
                            $result = $enhancer->suggestPhotoPoolMetadataBilingual(
                                $state['preliminary_title'],
                                $state['preliminary_date'],
                                $state['preliminary_description']
                            );

                            $set('title_cs', $result['cs']['title']);
                            $set('title_en', $result['en']['title']);
                            $set('description_cs', $result['cs']['description']);
                            $set('description_en', $result['en']['description']);
                            $set('event_date', $result['date']);
                            $set('slug', $result['slug']);

                            // SEO Metadata
                            $set('seo.title.cs', $result['cs']['seo']['title']);
                            $set('seo.description.cs', $result['cs']['seo']['description']);
                            $set('seo.keywords.cs', $result['cs']['seo']['keywords']);
                            $set('seo.og_title.cs', $result['cs']['seo']['og_title']);
                            $set('seo.og_description.cs', $result['cs']['seo']['og_description']);

                            $set('seo.title.en', $result['en']['seo']['title']);
                            $set('seo.description.en', $result['en']['seo']['description']);
                            $set('seo.keywords.en', $result['en']['seo']['keywords']);
                            $set('seo.og_title.en', $result['en']['seo']['og_title']);
                            $set('seo.og_description.en', $result['en']['seo']['og_description']);
                        }),

                    Step::make('AI Návrh & Revize')
                        ->label(__('admin.navigation.resources.photo_pool.steps.review.label'))
                        ->description(__('admin.navigation.resources.photo_pool.steps.review.description'))
                        ->icon(new HtmlString('<i class="fa-light fa-language"></i>'))
                        ->schema([
                            Actions::make([
                                Action::make('regenerateAi')
                                    ->label(__('admin.navigation.resources.photo_pool.actions.regenerate_ai'))
                                    ->icon(new HtmlString('<i class="fa-light fa-wand-magic-sparkles"></i>'))
                                    ->color('info')
                                    ->action(function ($get, $set, AiTextEnhancer $enhancer) {
                                        $result = $enhancer->suggestPhotoPoolMetadataBilingual(
                                            $get('title_cs') ?? '',
                                            $get('event_date'),
                                            $get('description_cs') ?? ''
                                        );

                                        $set('title_cs', $result['cs']['title']);
                                        $set('title_en', $result['en']['title']);
                                        $set('description_cs', $result['cs']['description']);
                                        $set('description_en', $result['en']['description']);
                                        $set('event_date', $result['date']);
                                        $set('slug', $result['slug']);

                                        // SEO Metadata
                                        $set('seo.title.cs', $result['cs']['seo']['title']);
                                        $set('seo.description.cs', $result['cs']['seo']['description']);
                                        $set('seo.keywords.cs', $result['cs']['seo']['keywords']);
                                        $set('seo.og_title.cs', $result['cs']['seo']['og_title']);
                                        $set('seo.og_description.cs', $result['cs']['seo']['og_description']);

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
                            Grid::make(2)->schema([
                                DatePicker::make('event_date')
                                    ->label(__('admin.navigation.resources.photo_pool.fields.event_date'))
                                    ->required()
                                    ->native(false),
                                TextInput::make('slug')
                                    ->label(__('admin.navigation.resources.photo_pool.fields.slug'))
                                    ->required(),
                            ]),
                            Tabs::make('Translations')
                                ->tabs([
                                    Tabs\Tab::make('Čeština')
                                        ->icon(new HtmlString('<i class="fa-light fa-flag-checkered"></i>'))
                                        ->schema([
                                            TextInput::make('title_cs')
                                                ->label(__('admin.navigation.resources.photo_pool.fields.title_cs'))
                                                ->required(),
                                            Textarea::make('description_cs')
                                                ->label(__('admin.navigation.resources.photo_pool.fields.description_cs'))
                                                ->rows(4),
                                        ]),
                                    Tabs\Tab::make('English')
                                        ->icon(new HtmlString('<i class="fa-light fa-flag-usa"></i>'))
                                        ->schema([
                                            TextInput::make('title_en')
                                                ->label(__('admin.navigation.resources.photo_pool.fields.title_en'))
                                                ->required(),
                                            Textarea::make('description_en')
                                                ->label(__('admin.navigation.resources.photo_pool.fields.description_en'))
                                                ->rows(4),
                                        ]),
                                    Tabs\Tab::make('SEO')
                                        ->icon(new HtmlString('<i class="fa-light fa-globe"></i>'))
                                        ->schema([
                                            \Filament\Schemas\Components\Group::make()
                                                ->statePath('seo')
                                                ->schema([
                                                    \App\Filament\Forms\CmsForms::getSeoSection(false),
                                                ]),
                                        ]),
                                ]),
                        ]),

                    Step::make('Nahrávání fotografií')
                        ->label(__('admin.navigation.resources.photo_pool.steps.upload.label'))
                        ->description(__('admin.navigation.resources.photo_pool.steps.upload.description'))
                        ->icon(new HtmlString('<i class="fa-light fa-images"></i>'))
                        ->schema([
                            FileUpload::make('photos')
                                ->label(__('admin.navigation.resources.photo_pool.fields.photos'))
                                ->placeholder(CmsForms::getUploadPlaceholder('Klikněte nebo přetáhněte fotografie sem'))
                                ->multiple()
                                ->image()
                                ->panelLayout('grid')
                                ->reorderable()
                                ->disk(config('filesystems.uploads.disk'))
                                ->directory(trim(config('filesystems.uploads.dir', 'uploads'), '/') . '/photo_pools/incoming')
                                ->maxFiles(200)
                                ->maxSize(30720)
                                ->maxParallelUploads(10)
                                ->imageResizeTargetWidth('1920')
                                ->imageResizeTargetHeight('1920')
                                ->imageResizeMode('contain')
                                ->imageResizeUpscale(false)
                                ->uploadingMessage(__('admin.navigation.resources.photo_pool.notifications.uploading'))
                                ->extraAttributes([
                                    'style' => 'max-height: 60vh; overflow-y: auto;',
                                    'x-on:file-pond-init' => "console.log('KS DEBUG: FilePond inicializován')",
                                    'x-on:file-pond-add-file' => "console.log('KS DEBUG: Soubor přidán do fronty:', \$event.detail.file.filename)",
                                    'x-on:file-pond-process-file' => "console.log('KS DEBUG: Soubor úspěšně nahrán na server:', \$event.detail.file.filename)",
                                ])
                                ->getUploadedFileNameForStorageUsing(fn (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string => Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.strtolower($file->getClientOriginalExtension()))
                                ->helperText(new HtmlString('<div class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800 text-xs text-blue-700 dark:text-blue-300 flex items-start gap-3">'.IconHelper::render(IconHelper::INFO, 'fal')->toHtml().'<div><strong>Informace k optimalizaci:</strong> Fotografie jsou pro zvýšení rychlosti automaticky zmenšeny v prohlížeči a následně na serveru převedeny na WebP. Nahrávání probíhá paralelně (10 souborů najednou).</div></div>'))
                                ->required(),
                        ]),
                    ]),
                ])
                ->mutateFormDataUsing(function (array $data): array {
                    return [
                        'title' => [
                            'cs' => $data['title_cs'],
                            'en' => $data['title_en'],
                        ],
                        'description' => [
                            'cs' => $data['description_cs'],
                            'en' => $data['description_en'],
                        ],
                        'event_date' => $data['event_date'],
                        'slug' => $data['slug'],
                        'event_type' => 'other',
                        'is_public' => true,
                        'is_visible' => true,
                        'photos' => $data['photos'],
                        'teams' => $data['teams'] ?? [],
                        'seo' => $data['seo'] ?? [],
                    ];
                })
                ->after(function (array $data, PhotoPool $record, $livewire) {
                    if (!empty($data['teams'])) {
                        $record->teams()->sync($data['teams']);
                    }

                    if (!empty($data['seo'])) {
                        $record->seo()->create($data['seo']);
                    }

                    $files = $data['photos'] ?? [];
                    if (empty($files)) {
                        return;
                    }

                    // Použijeme službu pro přípravu importu (přesun souborů a naplnění fronty)
                    $importer = app(\App\Services\PhotoPoolImporter::class);
                    $importer->prepareForImport($record, $files);

                    // Informujeme uživatele
                    \Filament\Notifications\Notification::make()
                        ->title(__('admin.navigation.resources.photo_pool.notifications.uploading'))
                        ->info()
                        ->body('Fotografie byly nahrány. Budete přesměrováni na detail galerie pro dokončení zpracování.')
                        ->send();

                    // Přesměrujeme na editaci, aby polling mohl začít pracovat
                    return $livewire->redirect(PhotoPoolResource::getUrl('edit', ['record' => $record]));
                }),
        ];
    }
}
