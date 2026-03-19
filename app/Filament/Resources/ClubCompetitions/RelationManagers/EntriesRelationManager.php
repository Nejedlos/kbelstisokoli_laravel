<?php

namespace App\Filament\Resources\ClubCompetitions\RelationManagers;

use App\Filament\Resources\ClubCompetitions\Widgets\CompetitionLeaderboardWidget;
use App\Support\IconHelper;
use App\Support\Icons\AppIcon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class EntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'entries';

    protected static ?string $modelLabel = 'Záznam';

    protected static ?string $pluralModelLabel = 'Záznamy výsledků';

    public static function getTitle(?Model $ownerRecord, string $pageClass): string
    {
        return __('admin.resources.club_competition.sections.results');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Section::make(new HtmlString(IconHelper::render(AppIcon::INFO) . ' ' . __('admin.resources.club_competition.sections.general')))
                            ->schema([
                                DatePicker::make('entry_date')
                                    ->label(__('admin.resources.club_competition.entry_fields.entry_date'))
                                    ->default(now())
                                    ->required()
                                    ->native(false),

                                TextInput::make('value')
                                    ->label(__('admin.resources.club_competition.entry_fields.value'))
                                    ->numeric()
                                    ->required()
                                    ->prefix(new HtmlString(IconHelper::render(AppIcon::COMPETITIONS)))
                                    ->extraInputAttributes(['class' => 'text-2xl font-black text-primary-600']),

                                TextInput::make('source_note')
                                    ->label(__('admin.resources.club_competition.entry_fields.source_note'))
                                    ->placeholder('např. Zápas proti Sokolu')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(1),

                        Section::make(new HtmlString(IconHelper::render(AppIcon::USER) . ' ' . __('admin.resources.club_competition.leaderboard.participant')))
                            ->description(__('admin.resources.club_competition.entry_fields.player_id_help'))
                            ->schema([
                                Select::make('player_id')
                                    ->label(__('admin.resources.club_competition.entry_fields.player_id'))
                                    ->relationship('player', 'name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?? 'Uživatel bez jména (ID: ' . $record->id . ')')
                                    ->searchable()
                                    ->preload(),

                                Select::make('teams')
                                    ->label(__('admin.resources.team.plural_label'))
                                    ->relationship('teams', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('label')
                                    ->label(__('admin.resources.club_competition.entry_fields.label'))
                                    ->helperText(__('admin.resources.club_competition.entry_fields.label_help')),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Livewire::make(CompetitionLeaderboardWidget::class, [
                    'ownerRecord' => $this->getOwnerRecord(),
                ]),
                EmbeddedTable::make(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('entry_date')
                    ->label(__('admin.resources.club_competition.entry_fields.entry_date'))
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('displayName')
                    ->label(__('admin.resources.club_competition.leaderboard.participant'))
                    ->state(fn ($record) => $record->player?->name ?? ($record->teams->count() ? $record->teams->pluck('name')->join(', ') : null) ?? $record->label)
                    ->searchable(['label']),
                TextColumn::make('value')
                    ->label(__('admin.resources.club_competition.entry_fields.value'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('source_note')
                    ->label(__('admin.resources.club_competition.entry_fields.source_note'))
                    ->limit(30),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('admin.resources.club_competition.entry_fields.add_entry'))
                    ->icon(new HtmlString(IconHelper::render(AppIcon::CREATE)))
                    ->modalHeading(__('admin.resources.club_competition.entry_fields.new_entry_modal'))
                    ->after(fn ($livewire) => $livewire->dispatch('refreshLeaderboard')),
            ])
            ->recordActions([
                EditAction::make()
                    ->icon(new HtmlString(IconHelper::render(AppIcon::EDIT)))
                    ->after(fn ($livewire) => $livewire->dispatch('refreshLeaderboard')),
                DeleteAction::make()
                    ->icon(new HtmlString(IconHelper::render(AppIcon::DELETE)))
                    ->after(fn ($livewire) => $livewire->dispatch('refreshLeaderboard')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(fn ($livewire) => $livewire->dispatch('refreshLeaderboard')),
                ]),
            ])
            ->modifyQueryUsing(fn ($query) => $query->orderBy('entry_date', 'desc')->orderBy('value', 'desc'));
    }

}
