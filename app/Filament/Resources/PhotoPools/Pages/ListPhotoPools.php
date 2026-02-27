<?php

namespace App\Filament\Resources\PhotoPools\Pages;

use App\Filament\Resources\PhotoPools\PhotoPoolResource;
use App\Models\MediaAsset;
use App\Models\PhotoPool;
use App\Services\AiTextEnhancer;
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
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

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
                        ->content(fn () => view('components.loader.basketball', [
                            'id' => 'ks-basketball-loader',
                            'style' => 'display: none;',
                        ]))
                        ->columnSpanFull(),

                    Placeholder::make('processing_progress')
                        ->label('')
                        ->content(fn () => new HtmlString('<div wire:stream="ks-loader-progress-text" class="text-sm font-bold text-primary-600 dark:text-primary-400 text-center animate-pulse"></div>'))
                        ->columnSpanFull(),

                    \Filament\Forms\Components\Wizard::make([
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
                                ]),
                        ]),

                    Step::make('Nahrávání fotografií')
                        ->label(__('admin.navigation.resources.photo_pool.steps.upload.label'))
                        ->description(__('admin.navigation.resources.photo_pool.steps.upload.description'))
                        ->icon(new HtmlString('<i class="fa-light fa-images"></i>'))
                        ->schema([
                            FileUpload::make('photos')
                                ->label(__('admin.navigation.resources.photo_pool.fields.photos'))
                                ->placeholder(new \Illuminate\Support\HtmlString('<div class="flex flex-col items-center justify-center py-4 text-gray-500 dark:text-gray-400">' . \App\Support\IconHelper::render(\App\Support\IconHelper::UPLOAD, 'fal')->toHtml() . '<span class="text-sm font-medium mt-3">Klikněte nebo přetáhněte fotografie sem</span></div>'))
                                ->multiple()
                                ->image()
                                ->panelLayout('grid')
                                ->reorderable()
                                ->disk(config('filesystems.uploads.disk'))
                                ->directory('uploads/photos/pools')
                                ->maxFiles(200)
                                ->maxSize(30720)
                                ->uploadingMessage(__('admin.navigation.resources.photo_pool.notifications.uploading'))
                                ->extraAttributes([
                                    'style' => 'max-height: 60vh; overflow-y: auto;',
                                ])
                                ->getUploadedFileNameForStorageUsing(fn (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string => \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.strtolower($file->getClientOriginalExtension()))
                                ->helperText('Maximálně 200 fotek, každá do 30 MB. Budou automaticky převedeny na WebP.')
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
                    ];
                })
                ->after(function (array $data, PhotoPool $record, $livewire) {
                    if (!empty($data['teams'])) {
                        $record->teams()->sync($data['teams']);
                    }

                    $files = $data['photos'] ?? [];
                    if (empty($files)) {
                        return;
                    }

                    $uploaderId = auth()->id();

                    DB::transaction(function () use ($files, $record, $uploaderId, $livewire) {
                        $total = count($files);
                        $sort = 0;
                        $livewire->stream('ks-loader-progress', '', true);
                        $livewire->stream('ks-loader-progress-text', '', true);
                        foreach ($files as $path) {
                            try {
                                $sort++;
                                $progressMsg = __("admin.navigation.resources.photo_pool.notifications.processing")." ($sort / $total)";
                                $livewire->stream('ks-loader-progress', " ($sort / $total)");
                                $livewire->stream('ks-loader-progress-text', $progressMsg);

                                $diskName = config('filesystems.uploads.disk');
                                $disk = Storage::disk($diskName);

                                if (! $disk->exists($path)) {
                                    continue;
                                }

                                $fullPath = $disk->path($path);
                                $file = new \Illuminate\Http\File($fullPath);

                                $asset = new MediaAsset([
                                    'title' => (string) (brand_text($record->getTranslation('title', 'cs')).' #'.(++$sort)),
                                    'alt_text' => brand_text($record->getTranslation('title', 'cs')),
                                    'type' => 'image',
                                    'access_level' => 'public',
                                    'is_public' => true,
                                    'uploaded_by_id' => $uploaderId,
                                ]);
                                $asset->save();

                                $asset
                                    ->addMedia($file)
                                    ->toMediaCollection('default');

                                $record->mediaAssets()->attach($asset->id, [
                                    'sort_order' => $sort,
                                    'is_visible' => true,
                                    'caption_override' => null,
                                ]);
                            } catch (\Throwable $e) {
                                \Log::error('Photo import failed in CreateAction: '.$e->getMessage());
                            }
                        }
                    });
                }),
        ];
    }
}
