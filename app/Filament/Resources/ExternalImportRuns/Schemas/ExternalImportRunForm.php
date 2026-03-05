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
                            ->rows(10)
                            ->extraAttributes([
                                'style' => 'font-family: monospace; font-size: 0.75rem; line-height: 1rem;',
                            ])
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
                                    ->extraAttributes([
                                        'x-data' => '{ copied: false }',
                                    ])
                                    ->url('#')
                                    ->alpineClickHandler("window.navigator.clipboard.writeText(\$el.closest('.fi-fo-field').querySelector('textarea').value); copied = true; setTimeout(() => copied = false, 2000); \$tooltip('Zkopírováno', { timeout: 2000 })")
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
