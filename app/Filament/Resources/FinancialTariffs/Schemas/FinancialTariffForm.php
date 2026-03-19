<?php

namespace App\Filament\Resources\FinancialTariffs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class FinancialTariffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.financial_tariff.sections.general'))
                    ->components([
                        TextInput::make('name')
                            ->label(__('admin.resources.financial_tariff.fields.name'))
                            ->placeholder(__('admin.resources.financial_tariff.placeholders.name'))
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label(__('admin.resources.financial_tariff.fields.type'))
                            ->options([
                                'flat' => __('admin.resources.financial_tariff.types.flat'),
                                'per_event' => __('admin.resources.financial_tariff.types.per_event'),
                                'prepaid' => __('admin.resources.financial_tariff.types.prepaid'),
                            ])
                            ->default('flat')
                            ->required()
                            ->live(),
                        TextInput::make('base_amount')
                            ->label(fn ($get) => match($get('type')) {
                                'per_event' => __('admin.resources.financial_tariff.fields.amount_per_event'),
                                'prepaid' => __('admin.resources.financial_tariff.fields.amount_per_package'),
                                default => __('admin.resources.financial_tariff.fields.base_amount')
                            })
                            ->numeric()
                            ->required()
                            ->prefix('CZK'),
                        TextInput::make('prepaid_events_count')
                            ->label(__('admin.resources.financial_tariff.fields.prepaid_events_count'))
                            ->numeric()
                            ->required(fn ($get) => $get('type') === 'prepaid')
                            ->visible(fn ($get) => $get('type') === 'prepaid'),
                        TextInput::make('extra_event_amount')
                            ->label(__('admin.resources.financial_tariff.fields.extra_event_amount'))
                            ->numeric()
                            ->required(fn ($get) => $get('type') === 'prepaid')
                            ->visible(fn ($get) => $get('type') === 'prepaid')
                            ->prefix('CZK'),
                        Select::make('unit')
                            ->label(__('admin.resources.financial_tariff.fields.unit'))
                            ->options([
                                'month' => __('admin.resources.financial_tariff.units.month'),
                                'season' => __('admin.resources.financial_tariff.units.season'),
                                'event' => __('admin.resources.financial_tariff.units.event'),
                            ])
                            ->default('month')
                            ->required(),
                    ])->columns(2),

                Section::make(__('admin.resources.financial_tariff.sections.installments'))
                    ->description(__('admin.resources.financial_tariff.descriptions.installments'))
                    ->visible(fn ($get) => $get('type') === 'flat')
                    ->components([
                        Repeater::make('installment_plan')
                            ->label(__('admin.resources.financial_tariff.fields.installment_plan'))
                            ->schema([
                                TextInput::make('label')
                                    ->label(__('admin.resources.financial_tariff.fields.installment_label'))
                                    ->placeholder(__('admin.resources.financial_tariff.placeholders.installment_label'))
                                    ->required(),
                                TextInput::make('amount')
                                    ->label(__('admin.resources.financial_tariff.fields.amount'))
                                    ->numeric()
                                    ->required()
                                    ->prefix('CZK'),
                                DatePicker::make('due_date')
                                    ->label(__('admin.resources.financial_tariff.fields.due_date'))
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel(__('admin.resources.financial_tariff.add_installment')),
                    ]),

                Section::make(__('admin.resources.financial_tariff.sections.automation'))
                    ->description(__('admin.resources.financial_tariff.descriptions.automation'))
                    ->icon(new HtmlString('<i class="fa-light fa-robot text-primary-500"></i>'))
                    ->components([
                        Toggle::make('calculate_attendance_fines')
                            ->label(__('admin.resources.financial_tariff.fields.calculate_attendance_fines'))
                            ->helperText(__('admin.resources.financial_tariff.helpers.attendance_fines'))
                            ->default(false),
                        Toggle::make('calculate_th_fines')
                            ->label(__('admin.resources.financial_tariff.fields.calculate_th_fines'))
                            ->helperText(__('admin.resources.financial_tariff.helpers.th_fines'))
                            ->default(false),
                    ])->columns(2),

                Section::make(__('admin.resources.financial_tariff.sections.additional'))
                    ->components([
                        Textarea::make('description')
                            ->label(__('admin.resources.financial_tariff.fields.description'))
                            ->rows(3),
                    ]),
            ]);
    }
}
