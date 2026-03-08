<?php

namespace App\Filament\Resources\FeedbackReports\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Schema;
use App\Support\Icons\AppIcon;
use App\Support\FilamentIcon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class FeedbackReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make(__('admin.resources.feedback_report.fields.summary'))
                    ->tabs([
                        Tabs\Tab::make(__('admin.resources.feedback_report.fields.summary'))
                            ->icon(FilamentIcon::get(AppIcon::LIST))
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make(__('admin.resources.feedback_report.fields.basic_info'))
                                            ->schema([
                                                TextInput::make('title')
                                                    ->label(__('admin.resources.feedback_report.fields.title'))
                                                    ->disabled(),
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('type')
                                                            ->label(__('admin.resources.feedback_report.fields.type'))
                                                            ->formatStateUsing(fn (string $state): string => __("admin.resources.feedback_report.type.{$state}"))
                                                            ->disabled(),
                                                        TextInput::make('severity')
                                                            ->label(__('admin.resources.feedback_report.fields.severity'))
                                                            ->formatStateUsing(fn (?string $state): string => $state ? __("admin.resources.feedback_report.severity.{$state}") : '-')
                                                            ->disabled(),
                                                    ]),
                                                Textarea::make('description')
                                                    ->label(__('admin.resources.feedback_report.fields.description'))
                                                    ->rows(5)
                                                    ->disabled(),
                                                Textarea::make('steps')
                                                    ->label(__('admin.resources.feedback_report.fields.steps'))
                                                    ->rows(3)
                                                    ->disabled()
                                                    ->visible(fn ($record) => !empty($record?->steps)),
                                            ])->columnSpan(1),

                                        Section::make(__('admin.resources.feedback_report.fields.status_and_notes'))
                                            ->schema([
                                                Select::make('status')
                                                    ->label(__('admin.resources.feedback_report.fields.status'))
                                                    ->options([
                                                        'new' => __('admin.resources.feedback_report.status.new'),
                                                        'triaging' => __('admin.resources.feedback_report.status.triaging'),
                                                        'in_progress' => __('admin.resources.feedback_report.status.in_progress'),
                                                        'resolved' => __('admin.resources.feedback_report.status.resolved'),
                                                        'wont_fix' => __('admin.resources.feedback_report.status.wont_fix'),
                                                    ])
                                                    ->required(),
                                                Textarea::make('admin_notes')
                                                    ->label(__('admin.resources.feedback_report.fields.admin_notes'))
                                                    ->rows(8)
                                                    ->placeholder(__('admin.resources.feedback_report.fields.admin_notes')),
                                            ])->columnSpan(1),
                                    ]),

                                Section::make(__('admin.resources.feedback_report.fields.context'))
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Placeholder::make('user_name')
                                                    ->label(__('admin.resources.feedback_report.fields.user'))
                                                    ->content(fn ($record) => $record?->user?->name),
                                                Placeholder::make('source_area')
                                                    ->label(__('admin.resources.feedback_report.fields.source_area'))
                                                    ->content(fn ($record) => $record ? ucfirst($record->source_area) : null),
                                                Placeholder::make('app_version')
                                                    ->label(__('admin.resources.feedback_report.fields.app_version'))
                                                    ->content(fn ($record) => $record?->app_version),
                                                Placeholder::make('url')
                                                    ->label(__('admin.resources.feedback_report.fields.url'))
                                                    ->content(fn ($record) => $record ? new HtmlString("<a href='{$record->url}' target='_blank' class='text-primary-600 underline'>{$record->url}</a>") : null),
                                                Placeholder::make('ip')
                                                    ->label(__('admin.resources.feedback_report.fields.ip'))
                                                    ->content(fn ($record) => $record?->ip),
                                                Placeholder::make('created_at')
                                                    ->label(__('admin.resources.feedback_report.fields.created_at'))
                                                    ->content(fn ($record) => $record?->created_at?->format('d.m.Y H:i:s')),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make(__('admin.resources.feedback_report.fields.screenshot'))
                            ->icon(FilamentIcon::get(AppIcon::MEDIA_LIBRARY))
                            ->schema([
                                Placeholder::make('screenshot')
                                    ->label('')
                                    ->content(fn ($record) => ($record && $record->screenshot_path)
                                        ? new HtmlString("<img src='" . Storage::url($record->screenshot_path) . "' class='max-w-full rounded-xl shadow-lg' />")
                                        : __('admin.resources.feedback_report.fields.no_screenshot')),
                            ]),
                        Tabs\Tab::make(__('admin.resources.feedback_report.fields.dom_snapshot'))
                            ->icon(FilamentIcon::get(AppIcon::CODE))
                            ->schema([
                                Placeholder::make('dom_snapshot')
                                    ->label('')
                                    ->content(fn ($record) => $record?->dom_path && Storage::exists($record->dom_path)
                                        ? new HtmlString("<pre class='p-4 bg-slate-900 text-emerald-400 rounded-xl overflow-x-auto text-xs max-h-[600px]'>" . e(Storage::get($record->dom_path)) . "</pre>")
                                        : __('admin.resources.feedback_report.fields.no_dom_snapshot')),
                            ]),

                        Tabs\Tab::make(__('admin.resources.feedback_report.fields.console_logs'))
                            ->icon(FilamentIcon::get(AppIcon::TERMINAL))
                            ->schema([
                                ViewField::make('logs_content')
                                    ->label('')
                                    ->view('filament.admin.feedback-logs')
                                    ->afterStateHydrated(function (ViewField $component, $record) {
                                        if ($record && $record->logs_path && Storage::exists($record->logs_path)) {
                                            $logs = json_decode(Storage::get($record->logs_path), true);
                                            $component->state(['logs' => $logs]);
                                        }
                                    }),
                            ]),

                        Tabs\Tab::make(__('admin.resources.feedback_report.fields.breadcrumbs'))
                            ->icon(FilamentIcon::get(AppIcon::SHOE_PRINTS))
                            ->schema([
                                ViewField::make('breadcrumbs_content')
                                    ->label('')
                                    ->view('filament.admin.feedback-logs')
                                    ->afterStateHydrated(function (ViewField $component, $record) {
                                        if ($record && $record->breadcrumbs_path && Storage::exists($record->breadcrumbs_path)) {
                                            $data = json_decode(Storage::get($record->breadcrumbs_path), true);
                                            $logs = array_map(fn($b) => [
                                                'type' => $b['type'],
                                                'timestamp' => $b['timestamp'],
                                                'data' => [
                                                    ($b['type'] === 'click' ? "Klik na <{$b['tag']}>: {$b['text']}" :
                                                     ($b['type'] === 'nav' ? "Navigace na: {$b['to']}" :
                                                      ($b['type'] === 'scroll' ? "Scroll depth: {$b['depth']}" :
                                                       ($b['type'] === 'submit' ? "Odeslání formuláře: {$b['form']}" : json_encode($b)))))
                                                ]
                                            ], $data);
                                            $component->state(['logs' => $logs]);
                                        }
                                    }),
                            ]),

                        Tabs\Tab::make(__('admin.resources.feedback_report.fields.network_and_clicks'))
                            ->icon(FilamentIcon::get(AppIcon::NETWORK))
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make(__('admin.resources.feedback_report.fields.network_errors'))
                                            ->schema([
                                                ViewField::make('network_logs_view')
                                                    ->label('')
                                                    ->view('filament.admin.feedback-logs')
                                                    ->afterStateHydrated(function (ViewField $component, $record) {
                                                        if ($record && $record->network_path && Storage::exists($record->network_path)) {
                                                            $data = json_decode(Storage::get($record->network_path), true);
                                                            $logs = array_map(fn($f) => [
                                                                'type' => 'error',
                                                                'timestamp' => $f['timestamp'],
                                                                'data' => ["{$f['method']} {$f['url']} - Status: {$f['status']} ({$f['duration_ms']}ms)", $f['error'] ?? null]
                                                            ], $data);
                                                            $component->state(['logs' => $logs]);
                                                        }
                                                    }),
                                            ]),
                                        Section::make(__('admin.resources.feedback_report.fields.detailed_clicks'))
                                            ->schema([
                                                ViewField::make('click_logs_view')
                                                    ->label('')
                                                    ->view('filament.admin.feedback-logs')
                                                    ->afterStateHydrated(function (ViewField $component, $record) {
                                                        if ($record && $record->clicks_path && Storage::exists($record->clicks_path)) {
                                                            $data = json_decode(Storage::get($record->clicks_path), true);
                                                            $logs = array_map(fn($c) => [
                                                                'type' => 'info',
                                                                'timestamp' => $c['timestamp'],
                                                                'data' => ["Klik na: {$c['element']} (Text: {$c['text']}) at [{$c['x']}, {$c['y']}]"]
                                                            ], $data);
                                                            $component->state(['logs' => $logs]);
                                                        }
                                                    }),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make(__('admin.resources.feedback_report.fields.performance'))
                            ->icon(FilamentIcon::get(AppIcon::GAUGE))
                            ->schema([
                                Placeholder::make('performance_data')
                                    ->label('')
                                    ->content(function ($record) {
                                        if (!$record?->performance_path || !Storage::exists($record->performance_path)) return __('admin.resources.feedback_report.fields.no_performance_data');
                                        $perf = json_decode(Storage::get($record->performance_path), true);
                                        return new HtmlString("<pre class='p-4 bg-slate-900 text-emerald-400 rounded-xl overflow-x-auto text-xs'>" . json_encode($perf, JSON_PRETTY_PRINT) . "</pre>");
                                    }),
                            ]),

                        Tabs\Tab::make(__('admin.resources.feedback_report.fields.meta'))
                            ->icon(FilamentIcon::get(AppIcon::CODE))
                            ->schema([
                                Placeholder::make('meta_raw')
                                    ->label('')
                                    ->content(fn ($record) => $record ? new HtmlString("<pre class='p-4 bg-slate-900 text-emerald-400 rounded-xl overflow-x-auto text-xs'>" . json_encode($record->meta, JSON_PRETTY_PRINT) . "</pre>") : null),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
