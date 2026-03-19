<?php

namespace App\Filament\Resources\PerformanceTestResults\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PerformanceTestResultForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.performance_test_result.sections.general'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('label')
                                    ->label(__('admin.resources.performance_test_result.fields.label'))
                                    ->required(),
                                TextInput::make('url')
                                    ->label(__('admin.resources.performance_test_result.fields.url'))
                                    ->required(),
                                Select::make('section')
                                    ->label(__('admin.resources.performance_test_result.fields.section'))
                                    ->options([
                                        'public' => __('admin.resources.performance_test_result.options.sections.public'),
                                        'member' => __('admin.resources.performance_test_result.options.sections.member'),
                                        'admin' => __('admin.resources.performance_test_result.options.sections.admin'),
                                    ])
                                    ->required(),
                                Select::make('scenario')
                                    ->label(__('admin.resources.performance_test_result.fields.scenario'))
                                    ->options([
                                        'standard' => __('admin.resources.performance_test_result.options.scenarios.standard'),
                                        'aggressive' => __('admin.resources.performance_test_result.options.scenarios.aggressive'),
                                        'ultra' => __('admin.resources.performance_test_result.options.scenarios.ultra'),
                                    ])
                                    ->required(),
                            ]),
                    ]),
                Section::make(__('admin.resources.performance_test_result.sections.metrics'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('duration_ms')
                                    ->label(__('admin.resources.performance_test_result.fields.duration_ms'))
                                    ->numeric(),
                                TextInput::make('query_count')
                                    ->label(__('admin.resources.performance_test_result.fields.query_count'))
                                    ->numeric(),
                                TextInput::make('query_time_ms')
                                    ->label(__('admin.resources.performance_test_result.fields.query_time_ms'))
                                    ->numeric(),
                                TextInput::make('memory_mb')
                                    ->label(__('admin.resources.performance_test_result.fields.memory_mb'))
                                    ->numeric(),
                                Toggle::make('opcache_enabled')
                                    ->label(__('admin.resources.performance_test_result.fields.opcache_enabled')),
                            ]),
                    ]),
                DateTimePicker::make('created_at')
                    ->label(__('admin.resources.performance_test_result.fields.created_at'))
                    ->disabled(),
            ]);
    }
}
