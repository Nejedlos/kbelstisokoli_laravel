<?php

namespace App\Filament\Resources\Trainings\Schemas;

use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class TrainingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('sport')
                            ->label(__('admin.resources.training.fields.sport'))
                            ->options([
                                'basketball' => __('trainings.basketball'),
                                'volleyball' => __('trainings.volleyball'),
                            ])
                            ->default('basketball')
                            ->required(),
                        Select::make('teams')
                            ->label(__('admin.resources.training.fields.teams'))
                            ->relationship('teams', 'name', fn ($query) => $query->where('category', '!=', 'all'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('location')
                            ->label(__('admin.resources.training.fields.location'))
                            ->placeholder(__('admin.resources.training.fields.location_placeholder'))
                            ->default(null),
                        DateTimePicker::make('starts_at')
                            ->label(__('admin.resources.training.fields.starts_at'))
                            ->native(false)
                            ->required(),
                        DateTimePicker::make('ends_at')
                            ->label(__('admin.resources.training.fields.ends_at'))
                            ->native(false)
                            ->default(null),
                    ]),
                Textarea::make('notes')
                    ->label(__('admin.resources.training.fields.notes'))
                    ->helperText(__('admin.resources.training.fields.notes_helper'))
                    ->default(null)
                    ->columnSpanFull(),

                Section::make(__('admin.resources.training.fields.recurring.section_label'))
                    ->icon(FilamentIcon::render(AppIcon::REFRESH))
                    ->description(__('admin.resources.training.fields.recurring.section_description'))
                    ->schema([
                        Select::make('repeat_frequency')
                            ->label(__('admin.resources.training.fields.recurring.frequency'))
                            ->options([
                                'daily' => __('admin.resources.training.fields.recurring.frequency_daily'),
                                'weekly' => __('admin.resources.training.fields.recurring.frequency_weekly'),
                                'monthly' => __('admin.resources.training.fields.recurring.frequency_monthly'),
                            ])
                            ->placeholder(__('admin.resources.training.fields.recurring.frequency_none'))
                            ->selectablePlaceholder()
                            ->live()
                            ->dehydrated(false),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('repeat_count')
                                    ->label(__('admin.resources.training.fields.recurring.count'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(50)
                                    ->helperText(__('admin.resources.training.fields.recurring.count_helper'))
                                    ->dehydrated(false),
                                Select::make('repeat_period')
                                    ->label(__('admin.resources.training.fields.recurring.period'))
                                    ->options([
                                        '1_month' => __('admin.resources.training.fields.recurring.period_1_month'),
                                        '2_months' => __('admin.resources.training.fields.recurring.period_2_months'),
                                        '3_months' => __('admin.resources.training.fields.recurring.period_3_months'),
                                        '6_months' => __('admin.resources.training.fields.recurring.period_6_months'),
                                        'this_season' => __('admin.resources.training.fields.recurring.period_this_season'),
                                    ])
                                    ->dehydrated(false),
                            ])
                            ->visible(fn (callable $get) => $get('repeat_frequency') !== null),
                    ])
                    ->hiddenOn('edit')
                    ->collapsible(),
            ]);
    }
}
