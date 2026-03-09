<?php

namespace App\Filament\Resources\ClubEvents\Schemas;

use App\Services\AiTextEnhancer;
use App\Support\IconHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ClubEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní informace')
                    ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('title.cs')
                                            ->label('Název akce (CS)')
                                            ->required()
                                            ->placeholder('např. Valná hromada, Brigáda, Soustředění'),
                                        TextInput::make('title.en')
                                            ->label('Název akce (EN)')
                                            ->placeholder('e.g. General Assembly, Camp, Tournament'),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        Select::make('event_type')
                                            ->label('Typ akce')
                                            ->options([
                                                'social' => 'Společenská akce',
                                                'meeting' => 'Schůzka / Porada',
                                                'camp' => 'Soustředění / Kemp',
                                                'volunteer' => 'Dobrovolnická akce / Brigáda',
                                                'tournament' => 'Turnaj',
                                                'all' => 'Klubová akce / Pro všechny',
                                                'other' => 'Ostatní',
                                            ])
                                            ->default('other')
                                            ->required(),
                                        Select::make('teams')
                                            ->label('Určeno pro týmy')
                                            ->helperText('Ponechte prázdné, pokud je akce pro celý klub.')
                                            ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                                            ->multiple()
                                            ->searchable()
                                            ->preload(),
                                    ]),
                                TextInput::make('location')
                                    ->label('Místo konání')
                                    ->placeholder('např. Klubovna, Hala Kbely')
                                    ->default(null),
                            ]),

                Section::make('Čas a dostupnost')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label('Začátek akce')
                                    ->native(false)
                                    ->required(),
                                DateTimePicker::make('ends_at')
                                    ->label('Konec akce')
                                    ->native(false)
                                    ->required(),
                                Toggle::make('is_public')
                                    ->label('Veřejná akce?')
                                    ->helperText('Pokud je vypnuto, uvidí ji pouze přihlášení členové.')
                                    ->default(true)
                                    ->required(),
                                Toggle::make('rsvp_enabled')
                                    ->label('Povolit přihlašování (Docházku)?')
                                    ->helperText('Umožní členům potvrdit svou účast.')
                                    ->default(true)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Popis a poznámky')
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->headerActions([
                        Action::make('generateAi')
                            ->label('Generovat pomocí AI')
                            ->icon(new HtmlString(IconHelper::render(IconHelper::AI)))
                            ->color('info')
                            ->action(function ($get, $set, AiTextEnhancer $enhancer) {
                                $title = $get('title.cs') ?? $get('title.en') ?? 'Nová akce';
                                $type = $get('event_type') ?? 'other';
                                $currentDescription = $get('description.cs') ?? '';
                                $location = $get('location');
                                $startsAt = $get('starts_at');
                                $endsAt = $get('ends_at');

                                $result = $enhancer->suggestClubEventDescriptionBilingual($title, $type, $currentDescription, $location, $startsAt, $endsAt);

                                $set('description.cs', $result['cs']);
                                $set('description.en', $result['en']);

                                Notification::make()
                                    ->title('Popis byl vygenerován pomocí AI')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                RichEditor::make('description.cs')
                                    ->label('Detailní popis akce (CS)')
                                    ->columnSpanFull(),
                                RichEditor::make('description.en')
                                    ->label('Detailní popis akce (EN)')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
