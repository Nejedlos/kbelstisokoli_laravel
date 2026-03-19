<?php

namespace App\Filament\Resources\ClubCompetitions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

use App\Support\IconHelper;
use App\Support\Icons\AppIcon;
use Illuminate\Support\HtmlString;

class ClubCompetitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(new HtmlString(IconHelper::render(AppIcon::COMPETITIONS) . ' ' . __('admin.resources.club_competition.sections.general')))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Tabs::make('Translations')
                                    ->tabs([
                                        Tab::make('Čeština')
                                            ->icon(new HtmlString(IconHelper::render(AppIcon::GLOBE)))
                                            ->schema([
                                                TextInput::make('name.cs')
                                                    ->label(__('admin.resources.club_competition.fields.name') . ' (CS)')
                                                    ->helperText('Např. Lumír Trophy, Střelec měsíce')
                                                    ->required(),
                                                Textarea::make('description.cs')
                                                    ->label(__('admin.resources.club_competition.fields.description') . ' (CS)')
                                                    ->rows(3),
                                                Textarea::make('rules.cs')
                                                    ->label(__('admin.resources.club_competition.fields.rules') . ' (CS)')
                                                    ->rows(4),
                                            ]),
                                        Tab::make('English')
                                            ->icon(new HtmlString(IconHelper::render(AppIcon::GLOBE)))
                                            ->schema([
                                                TextInput::make('name.en')
                                                    ->label(__('admin.resources.club_competition.fields.name') . ' (EN)')
                                                    ->helperText('e.g. Scorer of the month'),
                                                Textarea::make('description.en')
                                                    ->label(__('admin.resources.club_competition.fields.description') . ' (EN)')
                                                    ->rows(3),
                                                Textarea::make('rules.en')
                                                    ->label(__('admin.resources.club_competition.fields.rules') . ' (EN)')
                                                    ->rows(4),
                                            ]),
                                    ])->columnSpanFull(),

                                TextInput::make('slug')
                                    ->label(__('admin.resources.club_competition.fields.slug'))
                                    ->required()
                                    ->unique('club_competitions', 'slug', ignoreRecord: true),
                            ]),
                    ]),

                Section::make(new HtmlString(IconHelper::render(AppIcon::SETTINGS) . ' ' . __('admin.resources.club_competition.sections.settings')))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('season_id')
                                    ->label(__('admin.resources.club_competition.fields.season'))
                                    ->relationship('season', 'name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?? 'Sezóna bez názvu (ID: ' . $record->id . ')')
                                    ->required()
                                    ->default(\App\Models\Season::where('is_active', true)->first()?->id),
                                Select::make('status')
                                    ->label(__('admin.resources.club_competition.fields.status'))
                                    ->options([
                                        'active' => 'Probíhá',
                                        'completed' => 'Ukončeno',
                                        'archived' => 'Archivováno',
                                    ])
                                    ->default('active')
                                    ->required(),
                            ]),
                        Toggle::make('is_public')
                            ->label(__('admin.resources.club_competition.fields.is_public'))
                            ->default(true),
                    ]),
            ]);
    }
}
