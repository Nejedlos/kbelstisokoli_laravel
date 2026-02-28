<?php

namespace App\Filament\Resources\Galleries\RelationManagers;

use App\Models\PhotoPool;
use App\Services\GalleryService;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class MediaAssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'mediaAssets';

    protected static ?string $title = 'Média v galerii';

    protected static ?string $modelLabel = 'Asset';

    protected static ?string $pluralModelLabel = 'Média';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('caption_override')
                    ->label('Vlastní popisek pro tuto galerii')
                    ->placeholder('Ponechte prázdné pro použití výchozího z knihovny'),
                Toggle::make('is_visible')
                    ->label('Viditelné')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                SpatieMediaLibraryImageColumn::make('file')
                    ->label('Náhled')
                    ->collection('default')
                    ->conversion('thumb')
                    ->circular(),

                TextColumn::make('title')
                    ->label('Název v knihovně')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('caption_override')
                    ->label('Override popisek')
                    ->placeholder('Výchozí'),

                IconColumn::make('is_visible')
                    ->label('Viditelné')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('fillFromPool')
                    ->label(new HtmlString('<i class="fa-light fa-shuffle mr-1"></i> Doplnit z poolu'))
                    ->form([
                        Select::make('photo_pool_id')
                            ->label('Pool')
                            ->native(false)
                            ->options(fn () => PhotoPool::query()
                                ->where('is_public', true)
                                ->where('is_visible', true)
                                ->orderBy('event_date', 'desc')
                                ->pluck('title', 'id')
                                ->map(fn ($t) => brand_text($t))
                                ->toArray()
                            )
                            ->placeholder('Všechny veřejné pooly')
                            ->searchable(),
                        TextInput::make('count')->label('Počet fotek')->numeric()->default(20)->minValue(1)->maxValue(200),
                    ])
                    ->action(function (array $data, GalleryService $service) {
                        /** @var \App\Models\Gallery $gallery */
                        $gallery = $this->getOwnerRecord();
                        $added = $service->fillFromPoolRandom($gallery, (int) ($data['count'] ?? 20), $data['photo_pool_id'] ?? null);
                        $this->notify('success', "$added fotek přidáno z poolu.");
                        $this->refreshTable();
                    }),
                AttachAction::make()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('sort_order')->numeric()->default(0),
                    ])
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order');
    }
}
