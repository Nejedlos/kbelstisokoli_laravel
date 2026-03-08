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
                            ->icon('fa-light-list')
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
                            ->icon('fa-light-image')
                            ->schema([
                                Placeholder::make('screenshot')
                                    ->label('')
                                    ->content(fn ($record) => $record->screenshot_path
                                        ? new HtmlString("<img src='" . Storage::url($record->screenshot_path) . "' class='max-w-full rounded-xl shadow-lg' />")
                                        : 'Žádný screenshot nebyl přiložen.'),
                            ]),

                        Tabs\Tab::make('Logy Konzole')
                            ->icon('fa-light-terminal')
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

                        Tabs\Tab::make('Síť a Kliky')
                            ->icon('fa-light-network-wired')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Section::make('Síťové chyby')
                                            ->schema([
                                                Placeholder::make('network_logs')
                                                    ->label('')
                                                    ->content(function ($record) {
                                                        if (!$record->network_path || !Storage::exists($record->network_path)) return 'Žádné záznamy.';
                                                        $data = json_decode(Storage::get($record->network_path), true);
                                                        return view('filament.admin.feedback-logs', ['logs' => array_map(fn($f) => ['type' => 'error', 'timestamp' => $f['timestamp'], 'data' => ["{$f['method']} {$f['url']} - Status: {$f['status']}"]], $data)])->render();
                                                    }),
                                            ]),
                                        Section::make('Klikání')
                                            ->schema([
                                                Placeholder::make('click_logs')
                                                    ->label('')
                                                    ->content(function ($record) {
                                                        if (!$record->clicks_path || !Storage::exists($record->clicks_path)) return 'Žádné záznamy.';
                                                        $data = json_decode(Storage::get($record->clicks_path), true);
                                                        return view('filament.admin.feedback-logs', ['logs' => array_map(fn($c) => ['type' => 'info', 'timestamp' => $c['timestamp'], 'data' => ["Klik na: {$c['element']} (Text: {$c['text']})"]], $data)])->render();
                                                    }),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('Meta')
                            ->icon('fa-light-code')
                            ->schema([
                                Placeholder::make('meta_raw')
                                    ->label('')
                                    ->content(fn ($record) => new HtmlString("<pre class='p-4 bg-slate-900 text-emerald-400 rounded-xl overflow-x-auto text-xs'>" . json_encode($record->meta, JSON_PRETTY_PRINT) . "</pre>")),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
