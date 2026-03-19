<?php

namespace App\Filament\Resources\ExternalTeamMappings\Schemas;

use App\Models\ExternalStatSource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExternalTeamMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.external_team_mapping.sections.general'))
                    ->columns(2)
                    ->schema([
                        Select::make('source_key')
                            ->label(__('admin.resources.external_team_mapping.fields.source'))
                            ->options(ExternalStatSource::all()->pluck('name', 'slug'))
                            ->default('czbasketball')
                            ->searchable()
                            ->required(),
                        Select::make('team_id')
                            ->label(__('admin.resources.external_team_mapping.fields.team'))
                            ->relationship('team', 'name')
                            ->required(),
                        TextInput::make('external_team_id')
                            ->label(__('admin.resources.external_team_mapping.fields.external_team_id'))
                            ->required()
                            ->helperText(__('admin.resources.external_team_mapping.helpers.external_team_id')),
                        TextInput::make('base_team_url')
                            ->label(__('admin.resources.external_team_mapping.fields.base_team_url'))
                            ->url()
                            ->required()
                            ->helperText(__('admin.resources.external_team_mapping.helpers.base_team_url')),
                    ]),
            ]);
    }
}
