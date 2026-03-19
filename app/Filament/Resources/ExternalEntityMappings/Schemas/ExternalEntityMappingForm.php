<?php

namespace App\Filament\Resources\ExternalEntityMappings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExternalEntityMappingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.external_entity_mapping.sections.external'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('source_key')
                            ->label(__('admin.resources.external_entity_mapping.fields.source'))
                            ->readOnly(),
                        TextInput::make('external_id')
                            ->label(__('admin.resources.external_entity_mapping.fields.external_id'))
                            ->readOnly(),
                        TextInput::make('identity_key')
                            ->label(__('admin.resources.external_entity_mapping.fields.identity_key'))
                            ->readOnly()
                            ->columnSpanFull(),
                        TextInput::make('metadata.player_name')
                            ->label(__('admin.resources.external_entity_mapping.fields.player_name'))
                            ->readOnly(),
                        TextInput::make('metadata.birth_year')
                            ->label(__('admin.resources.external_entity_mapping.fields.birth_year'))
                            ->readOnly(),
                    ]),
                Section::make(__('admin.resources.external_entity_mapping.sections.internal'))
                    ->schema([
                        Select::make('internal_id')
                            ->label(__('admin.resources.external_entity_mapping.fields.assigned_user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->helperText(__('admin.resources.external_entity_mapping.helpers.assigned_user')),
                    ]),
            ]);
    }
}
