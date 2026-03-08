<?php

namespace App\Filament\Resources\FeedbackReports\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Grid;
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
                Tabs::make('Feedback Details')
                    ->tabs([
                        Tabs\Tab::make('Souhrn')
                            ->icon(FilamentIcon::get(AppIcon::LIST))
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make('Základní informace')
                                            ->schema([
                                                TextInput::make('title')
                                                    ->label('Nadpis')
                                                    ->disabled(),
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('type')
                                                            ->label('Typ')
                                                            ->disabled(),
                                                        TextInput::make('severity')
                                                            ->label('Závažnost')
                                                            ->disabled(),
                                                    ]),
                                                Textarea::make('description')
                                                    ->label('Popis')
                                                    ->rows(5)
                                                    ->disabled(),
                                                Textarea::make('steps')
                                                    ->label('Kroky k reprodukci')
                                                    ->rows(3)
                                                    ->disabled()
                                                    ->visible(fn ($record) => !empty($record->steps)),
                                            ])->columnSpan(1),

                                        Section::make('Stav a Poznámky')
                                            ->schema([
                                                Select::make('status')
                                                    ->label('Stav')
                                                    ->options([
                                                        'new' => 'Nové',
                                                        'triaging' => 'Prověřování',
                                                        'in_progress' => 'V řešení',
                                                        'resolved' => 'Vyřešeno',
                                                        'wont_fix' => 'Nebude se řešit',
                                                    ])
                                                    ->required(),
                                                Textarea::make('admin_notes')
                                                    ->label('Poznámky administrátora')
                                                    ->rows(8)
                                                    ->placeholder('Zde můžete uvést interní poznámky k řešení...'),
                                            ])->columnSpan(1),
                                    ]),

                                Section::make('Kontext')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Placeholder::make('user_name')
                                                    ->label('Uživatel')
                                                    ->content(fn ($record) => $record->user?->name),
                                                Placeholder::make('source_area')
                                                    ->label('Oblast')
                                                    ->content(fn ($record) => ucfirst($record->source_area)),
                                                Placeholder::make('app_version')
                                                    ->label('Verze aplikace')
                                                    ->content(fn ($record) => $record->app_version),
                                                Placeholder::make('url')
                                                    ->label('URL')
                                                    ->content(fn ($record) => new HtmlString("<a href='{$record->url}' target='_blank' class='text-primary-600 underline'>{$record->url}</a>")),
                                                Placeholder::make('ip')
                                                    ->label('IP Adresa')
                                                    ->content(fn ($record) => $record->ip),
                                                Placeholder::make('created_at')
                                                    ->label('Vytvořeno')
                                                    ->content(fn ($record) => $record->created_at->format('d.m.Y H:i:s')),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('Screenshot')
                            ->icon(FilamentIcon::get(AppIcon::MEDIA_LIBRARY))
                            ->schema([
                                Placeholder::make('screenshot')
                                    ->label('')
                                    ->content(fn ($record) => $record->screenshot_path
                                        ? new HtmlString("<img src='" . Storage::url($record->screenshot_path) . "' class='max-w-full rounded-xl shadow-lg' />")
                                        : 'Žádný screenshot nebyl přiložen.'),
                            ]),

                        Tabs\Tab::make('DOM Snapshot')
                            ->icon(FilamentIcon::get(AppIcon::CODE))
                            ->schema([
                                Placeholder::make('dom_snapshot')
                                    ->label('')
                                    ->content(fn ($record) => $record->dom_path && Storage::exists($record->dom_path)
                                        ? new HtmlString("<pre class='p-4 bg-slate-900 text-emerald-400 rounded-xl overflow-x-auto text-xs max-h-[600px]'>" . e(Storage::get($record->dom_path)) . "</pre>")
                                        : 'Žádný DOM snapshot nebyl přiložen.'),
                            ]),

                        Tabs\Tab::make('Logy Konzole')
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

                        Tabs\Tab::make('Breadcrumbs')
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

                        Tabs\Tab::make('Síť a Kliky')
                            ->icon(FilamentIcon::get(AppIcon::NETWORK))
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make('Síťové chyby')
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
                                        Section::make('Detailní kliky')
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

                        Tabs\Tab::make('Výkon')
                            ->icon(FilamentIcon::get(AppIcon::GAUGE))
                            ->schema([
                                Placeholder::make('performance_data')
                                    ->label('')
                                    ->content(function ($record) {
                                        if (!$record->performance_path || !Storage::exists($record->performance_path)) return 'Žádná data.';
                                        $perf = json_decode(Storage::get($record->performance_path), true);
                                        return new HtmlString("<pre class='p-4 bg-slate-900 text-emerald-400 rounded-xl overflow-x-auto text-xs'>" . json_encode($perf, JSON_PRETTY_PRINT) . "</pre>");
                                    }),
                            ]),

                        Tabs\Tab::make('Meta')
                            ->icon(FilamentIcon::get(AppIcon::CODE))
                            ->schema([
                                Placeholder::make('meta_raw')
                                    ->label('')
                                    ->content(fn ($record) => new HtmlString("<pre class='p-4 bg-slate-900 text-emerald-400 rounded-xl overflow-x-auto text-xs'>" . json_encode($record->meta, JSON_PRETTY_PRINT) . "</pre>")),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
