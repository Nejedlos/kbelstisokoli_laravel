<?php

namespace App\Filament\Resources\BasketballMatches\Schemas;

use App\Filament\Resources\BasketballMatches\BasketballMatchResource;
use App\Models\BasketballMatch;
use App\Models\Season;
use App\Services\OpponentQuickCreateService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

class BasketballMatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní informace')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('match_type')
                                    ->label('Typ zápasu')
                                    ->options([
                                        'mistrovske' => 'Mistrovské utkání',
                                        'poharove' => 'Pohárové utkání',
                                        'pratelske' => 'Přátelské utkání',
                                    ])
                                    ->required()
                                    ->default('mistrovske')
                                    ->live(),
                                Select::make('teams')
                                    ->label('Týmy')
                                    ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->multiple(),
                                Select::make('season_id')
                                    ->label('Sezóna')
                                    ->relationship('season', 'name')
                                    ->required()
                                    ->default(Season::where('is_active', true)->first()?->id),
                                Select::make('opponent_id')
                                    ->label('Soupeř')
                                    ->relationship('opponent', 'name')
                                    ->getSearchResultsUsing(fn (string $search): array => app(OpponentQuickCreateService::class)->searchOptions($search))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function (Select $component, Set $set, mixed $state, ?BasketballMatch $record): void {
                                        $service = app(OpponentQuickCreateService::class);

                                        if (! $service->isCreateOption($state)) {
                                            return;
                                        }

                                        $record
                                            ? BasketballMatchResource::authorizeEdit($record)
                                            : BasketballMatchResource::authorizeCreate();

                                        try {
                                            $opponent = $service->createFromOption($state);
                                        } catch (ValidationException $exception) {
                                            $set('opponent_id', null);

                                            throw ValidationException::withMessages([
                                                $component->getStatePath() => collect($exception->errors())->flatten()->first()
                                                    ?? __('admin.resources.basketball_match.validation.invalid_opponent_option'),
                                            ]);
                                        }

                                        $set('opponent_id', (string) $opponent->getKey());
                                        $component->refreshSelectedOptionLabel();
                                    })
                                    ->exists('opponents', 'id')
                                    ->required(),
                                DateTimePicker::make('scheduled_at')
                                    ->label('Datum a čas zápasu')
                                    ->native(false)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Detaily zápasu')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('location')
                                    ->label('Místo konání')
                                    ->placeholder('např. Hala Kbely')
                                    ->default(null),
                                Toggle::make('is_home')
                                    ->label('Domácí zápas?')
                                    ->default(true)
                                    ->required(),
                                Select::make('status')
                                    ->label('Stav zápasu')
                                    ->options([
                                        'planned' => 'Plánováno',
                                        'finished' => 'Odehráno',
                                        'cancelled' => 'Zrušeno',
                                        'postponed' => 'Odloženo',
                                    ])
                                    ->required()
                                    ->default('planned'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('score_home')
                                    ->label('Skóre (Domácí)')
                                    ->numeric()
                                    ->default(null),
                                TextInput::make('score_away')
                                    ->label('Skóre (Hosté)')
                                    ->numeric()
                                    ->default(null),
                            ])
                            ->visible(fn ($get) => $get('status') === 'finished'),
                    ]),

                Section::make('Poznámky')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('notes_public')
                            ->label('Veřejná poznámka')
                            ->helperText('Zobrazí se fanouškům na webu.')
                            ->default(null)
                            ->columnSpanFull(),
                        Textarea::make('notes_internal')
                            ->label('Interní poznámka')
                            ->helperText('Pouze pro trenéry a adminy.')
                            ->default(null)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
