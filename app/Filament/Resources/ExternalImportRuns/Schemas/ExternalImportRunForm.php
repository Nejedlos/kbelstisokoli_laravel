<?php

namespace App\Filament\Resources\ExternalImportRuns\Schemas;

use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExternalImportRunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní informace')
                    ->columns(3)
                    ->schema([
                        TextInput::make('source_key')
                            ->label('Zdroj')
                            ->readOnly(),
                        TextInput::make('run_type')
                            ->label('Typ běhu')
                            ->readOnly(),
                        TextInput::make('status')
                            ->label('Stav')
                            ->readOnly(),
                        TextInput::make('season.name')
                            ->label('Sezóna')
                            ->readOnly(),
                        TextInput::make('team.name')
                            ->label('Tým')
                            ->readOnly(),
                        TextInput::make('target_external_id')
                            ->label('Cílové externí ID')
                            ->readOnly(),
                    ]),
                Section::make('Časové údaje')
                    ->columns(3)
                    ->schema([
                        TextInput::make('started_at')
                            ->label('Zahájeno')
                            ->readOnly(),
                        TextInput::make('finished_at')
                            ->label('Dokončeno')
                            ->readOnly(),
                        TextInput::make('created_at')
                            ->label('Vytvořeno')
                            ->readOnly(),
                    ]),
                Section::make('Výsledky')
                    ->columns(3)
                    ->schema([
                        TextInput::make('extracted_count')
                            ->label('Extrahováno')
                            ->numeric()
                            ->readOnly(),
                        TextInput::make('imported_count')
                            ->label('Importováno')
                            ->numeric()
                            ->readOnly(),
                        TextInput::make('skipped_count')
                            ->label('Přeskočeno')
                            ->numeric()
                            ->readOnly(),
                        TextInput::make('content_hash')
                            ->label('Hash obsahu')
                            ->columnSpanFull()
                            ->readOnly(),
                        Textarea::make('error_summary')
                            ->label('Chyba')
                            ->columnSpanFull()
                            ->readOnly()
                            ->rows(15)
                            ->extraAttributes([
                                'style' => 'font-family: "JetBrains Mono", "Courier New", monospace; font-size: 0.8rem; line-height: 1.2; background-color: #f9fafb;',
                            ])
                            ->helperText(fn ($record) => $record && isset($record->metadata['html_size']) ? "Velikost zdrojového HTML: " . number_format($record->metadata['html_size'] / 1024, 1) . " KB | Timeout: " . (config('services.openai.timeout') ?? 60) . "s" : null)
                            ->hintAction(
                                \Filament\Actions\ActionGroup::make([
                                    Action::make('copyError')
                                        ->label(new \Illuminate\Support\HtmlString('
                                            <span x-show="!copied">Kopírovat kompletní chybu</span>
                                            <span x-show="copied" x-cloak>Zkopírováno!</span>
                                        '))
                                        ->icon(new \Illuminate\Support\HtmlString('
                                            <span x-show="!copied">' . \App\Support\FilamentIcon::render(\App\Support\Icons\AppIcon::COPY) . '</span>
                                            <span x-show="copied" class="text-success-500" x-cloak>' . \App\Support\FilamentIcon::render(\App\Support\Icons\AppIcon::ACTIVATE) . '</span>
                                        '))
                                        ->extraAttributes([
                                            'x-data' => '{ copied: false }',
                                        ])
                                        ->url('#')
                                        ->alpineClickHandler("event.preventDefault(); window.navigator.clipboard.writeText(\$el.closest('.fi-fo-field').querySelector('textarea').value); copied = true; setTimeout(() => copied = false, 2000); \$tooltip('Zkopírováno do schránky', { timeout: 2000 })"),

                                    Action::make('downloadDebugHtml')
                                        ->label('Stáhnout zdrojové HTML')
                                        ->icon(new \Illuminate\Support\HtmlString(\App\Support\FilamentIcon::render(\App\Support\Icons\AppIcon::AUDIT_LOGS)))
                                        ->color('gray')
                                        ->action(function ($record) {
                                            if (isset($record->metadata['debug_html_file']) && \Illuminate\Support\Facades\Storage::disk('local')->exists($record->metadata['debug_html_file'])) {
                                                return \Illuminate\Support\Facades\Storage::disk('local')->download($record->metadata['debug_html_file'], "import_run_{$record->id}.html");
                                            }
                                        })
                                        ->hidden(fn ($record) => ! $record || ! isset($record->metadata['debug_html_file'])),
                                ])->dropdown(false)
                            )
                            ->hidden(fn ($get) => ! $get('error_summary')),
                    ]),
                Section::make('Metadata a doplňky')
                    ->schema([
                        KeyValue::make('metadata')
                            ->label('Metadata')
                            ->disableAddingRows()
                            ->disableDeletingRows()
                            ->disableEditingKeys()
                            ->disableEditingValues(),
                    ]),
            ]);
    }
}
