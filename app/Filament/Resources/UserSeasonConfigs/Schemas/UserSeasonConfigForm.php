<?php

namespace App\Filament\Resources\UserSeasonConfigs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserSeasonConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.user_season_config.sections.general'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label(__('admin.resources.user_season_config.fields.user'))
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->hidden(fn ($livewire) => $livewire instanceof RelationManager),
                                Select::make('season_id')
                                    ->label(__('admin.resources.user_season_config.fields.season'))
                                    ->relationship('season', 'name')
                                    ->required()
                                    ->default(\App\Models\Season::where('is_active', true)->first()?->id),
                                Select::make('financial_tariff_id')
                                    ->label(__('admin.resources.user_season_config.fields.tariff'))
                                    ->relationship('tariff', 'name')
                                    ->required(),
                                TextInput::make('opening_balance')
                                    ->label(__('admin.resources.user_season_config.fields.opening_balance'))
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Kč')
                                    ->helperText(__('admin.resources.user_season_config.helpers.opening_balance')),
                            ]),
                    ]),

                Section::make(__('admin.resources.user_season_config.sections.billing_and_exemptions'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('billing_start_month')
                                    ->label(__('admin.resources.user_season_config.fields.billing_start_month'))
                                    ->options(self::getMonthsOptions())
                                    ->nullable(),
                                Select::make('billing_end_month')
                                    ->label(__('admin.resources.user_season_config.fields.billing_end_month'))
                                    ->options(self::getMonthsOptions())
                                    ->nullable(),
                                Select::make('exemption_start_month')
                                    ->label(__('admin.resources.user_season_config.fields.exemption_start_month'))
                                    ->options(self::getMonthsOptions())
                                    ->nullable(),
                                Select::make('exemption_end_month')
                                    ->label(__('admin.resources.user_season_config.fields.exemption_end_month'))
                                    ->options(self::getMonthsOptions())
                                    ->nullable(),
                            ]),
                    ]),

                Section::make(__('admin.resources.user_season_config.sections.other'))
                    ->schema([
                        Toggle::make('track_attendance')
                            ->label(__('admin.resources.user_season_config.fields.track_attendance'))
                            ->helperText(__('admin.resources.user_season_config.helpers.track_attendance'))
                            ->default(true),
                    ]),
            ]);
    }

    protected static function getMonthsOptions(): array
    {
        return [
            1 => __('admin.months.1'),
            2 => __('admin.months.2'),
            3 => __('admin.months.3'),
            4 => __('admin.months.4'),
            5 => __('admin.months.5'),
            6 => __('admin.months.6'),
            7 => __('admin.months.7'),
            8 => __('admin.months.8'),
            9 => __('admin.months.9'),
            10 => __('admin.months.10'),
            11 => __('admin.months.11'),
            12 => __('admin.months.12'),
        ];
    }
}
