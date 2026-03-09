<?php

namespace App\Filament\Resources\ExternalImportRuns\Schemas;

use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
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
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'team_page' => 'Tým (soupiska)',
                                'matches_list' => 'Seznam zápasů',
                                'match_detail' => 'Detail zápasu',
                                'preview' => 'Náhled (preview)',
                                default => $state,
                            })
                            ->readOnly(),
                        TextInput::make('status')
                            ->label('Stav')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'success' => 'Úspěch',
                                'skipped' => 'Přeskočeno',
                                'failed' => 'Chyba',
                                'partial_failed' => 'Částečná chyba',
                                'running' => 'Běží',
                                default => $state,
                            })
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
                                'style' => 'font-family: "JetBrains Mono", "Courier New", monospace; font-size: 0.8rem; line-height: 1.2; background-color: #fef2f2;',
                            ])
                            ->helperText(fn ($record) => $record && isset($record->metadata['html_size']) ? "Původní HTML: " . number_format($record->metadata['html_size'] / 1024, 1) . " KB | Sanitizováno: " . (isset($record->metadata['sanitized_length']) ? number_format($record->metadata['sanitized_length'] / 1024, 1) . " KB" : "N/A") . " | Timeout: " . (config('services.openai.timeout') ?? 60) . "s | Connect: 10s" : null)
                            ->hintAction(
                                Action::make('copyError')
                                    ->label(new \Illuminate\Support\HtmlString('
                                        <span x-show="!copied">Kopírovat chybu</span>
                                        <span x-show="copied" x-cloak>Zkopírováno!</span>
                                    '))
                                    ->icon(new \Illuminate\Support\HtmlString('
                                        <span x-show="!copied">' . \App\Support\FilamentIcon::render(\App\Support\Icons\AppIcon::COPY) . '</span>
                                        <span x-show="copied" class="text-success-500" x-cloak>' . \App\Support\FilamentIcon::render(\App\Support\Icons\AppIcon::ACTIVATE) . '</span>
                                    '))
                                    ->color('primary')
                                    ->extraAttributes([
                                        'x-data' => '{ copied: false }',
                                        'class' => 'font-bold'
                                    ])
                                    ->url('#')
                                    ->alpineClickHandler("event.preventDefault(); window.navigator.clipboard.writeText(\$el.closest('.fi-fo-field').querySelector('textarea').value); copied = true; setTimeout(() => copied = false, 2000); \$tooltip('Zkopírováno do schránky', { timeout: 2000 })")
                            )
                            ->hintAction(
                                Action::make('downloadDebugHtml')
                                    ->label('Stáhnout zdrojové HTML')
                                    ->icon(new \Illuminate\Support\HtmlString(\App\Support\FilamentIcon::render(\App\Support\Icons\AppIcon::AUDIT_LOGS)))
                                    ->color('info')
                                    ->action(function ($record) {
                                        if (isset($record->metadata['debug_html_file']) && \Illuminate\Support\Facades\Storage::disk('local')->exists($record->metadata['debug_html_file'])) {
                                            return \Illuminate\Support\Facades\Storage::disk('local')->download($record->metadata['debug_html_file'], "import_run_{$record->id}.html");
                                        }
                                    })
                                    ->hidden(fn ($record) => ! $record || ! isset($record->metadata['debug_html_file']))
                            )
                            ->hidden(fn ($get) => ! $get('error_summary')),
                        Textarea::make('metadata.debug_logs')
                            ->label('Debug logy (OpenAI)')
                            ->columnSpanFull()
                            ->readOnly()
                            ->rows(10)
                            ->extraAttributes([
                                'style' => 'font-family: "JetBrains Mono", "Courier New", monospace; font-size: 0.75rem; line-height: 1.1; background-color: #f0fdf4;',
                            ])
                            ->formatStateUsing(function ($state) {
                                if (is_array($state)) {
                                    return implode("\n", $state);
                                }
                                return $state;
                            })
                            ->hidden(fn ($record) => ! $record || ! isset($record->metadata['debug_logs'])),
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
                Section::make('Provedené změny v datech')
                    ->schema([
                        Repeater::make('logs')
                            ->relationship('logs')
                            ->label('Logy změn')
                            ->schema([
                                TextInput::make('action')
                                    ->label('Akce')
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'created' => 'Vytvořeno',
                                        'updated' => 'Aktualizováno',
                                        'skipped' => 'Přeskočeno',
                                        'error' => 'Chyba',
                                        default => $state ?? '-',
                                    })
                                    ->readOnly(),
                                TextInput::make('model_type')
                                    ->label('Model')
                                    ->formatStateUsing(fn (?string $state) => $state ? match (class_basename($state)) {
                                        'Player' => 'Hráč',
                                        'Team' => 'Tým',
                                        'Season' => 'Sezóna',
                                        'Match' => 'Zápas',
                                        'Opponent' => 'Soupeř',
                                        'Club' => 'Klub',
                                        'ExternalImportLog' => 'Log importu',
                                        'ExternalImportRun' => 'Běh importu',
                                        default => class_basename($state)
                                    } : '-')
                                    ->readOnly(),
                                TextInput::make('model_id')
                                    ->label('ID')
                                    ->readOnly(),
                                Textarea::make('message')
                                    ->label('Zpráva')
                                    ->columnSpanFull()
                                    ->readOnly()
                                    ->rows(2)
                                    ->hidden(fn ($state) => ! $state),
                                KeyValue::make('new_values')
                                    ->label('Nová/Změněná data')
                                    ->columnSpanFull()
                                    ->disableAddingRows()
                                    ->disableDeletingRows()
                                    ->disableEditingKeys()
                                    ->disableEditingValues()
                                    ->hidden(fn ($state) => empty($state)),
                            ])
                            ->columns(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string =>
                                ($state['action'] === 'created' ? '🆕 ' : ($state['action'] === 'updated' ? '📝 ' : 'ℹ️ ')) .
                                (match ($state['action'] ?? '') {
                                    'created' => 'Vytvořeno',
                                    'updated' => 'Aktualizováno',
                                    'skipped' => 'Přeskočeno',
                                    'error' => 'Chyba',
                                    default => $state['action'] ?? 'Log',
                                }) . ': ' .
                                ($state['model_type'] ? match (class_basename($state['model_type'])) {
                                    'Player' => 'Hráč',
                                    'Team' => 'Tým',
                                    'Season' => 'Sezóna',
                                    'Match' => 'Zápas',
                                    'Opponent' => 'Soupeř',
                                    'Club' => 'Klub',
                                    default => class_basename($state['model_type'])
                                } : '') .
                                ($state['model_id'] ? ' (#' . $state['model_id'] . ')' : '')
                            )
                    ])
                    ->collapsible()
                    ->hidden(fn ($record) => ! $record || $record->logs()->count() === 0),
            ]);
    }
}
