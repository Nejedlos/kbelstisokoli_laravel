<?php

namespace App\Filament\Resources\NotFoundLogs\Tables;

use App\Models\Redirect;
use App\Models\NotFoundLog;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class NotFoundLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->description(fn (NotFoundLog $record) => $record->referer ? 'Referer: ' . $record->referer : null),

                TextColumn::make('hits_count')
                    ->label('Výskyty')
                    ->badge()
                    ->numeric()
                    ->sortable(),

                TextColumn::make('last_seen_at')
                    ->label('Naposledy')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'redirected' => 'success',
                        'ignored' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Čeká',
                        'redirected' => 'Přesměrováno',
                        'ignored' => 'Ignorováno',
                        default => $state,
                    })
                    ->sortable(),

                IconColumn::make('is_ignored')
                    ->label('Ignorováno')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ip_address')
                    ->label('IP / Agent')
                    ->description(fn ($record) => substr($record->user_agent, 0, 50) . '...')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'pending' => 'Čeká',
                        'redirected' => 'Přesměrováno',
                        'ignored' => 'Ignorováno',
                    ]),
                TernaryFilter::make('is_ignored')
                    ->label('Ignorováno'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('create_redirect')
                        ->label('Vytvořit přesměrování')
                        ->icon(new HtmlString('<i class="fa-light fa-shuffle"></i>'))
                        ->color('success')
                        ->form(function (NotFoundLog $record) {
                            // Tady později přidáme logiku pro návrh
                            $suggestion = self::suggestTarget($record->url);

                            return [
                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('source_path')
                                            ->label('Původní cesta')
                                            ->default($record->url)
                                            ->required(),
                                        TextInput::make('target_path')
                                            ->label('Cílová cesta (Návrh)')
                                            ->default($suggestion)
                                            ->helperText('Zadejte cestu začínající lomítkem.')
                                            ->required(),
                                        Select::make('status_code')
                                            ->label('Kód')
                                            ->options([
                                                301 => '301 - Trvalé',
                                                302 => '302 - Dočasné',
                                            ])
                                            ->default(301)
                                            ->required(),
                                    ]),
                            ];
                        })
                        ->action(function (NotFoundLog $record, array $data) {
                            $redirect = Redirect::create([
                                'source_path' => $data['source_path'],
                                'target_path' => $data['target_path'],
                                'status_code' => $data['status_code'],
                                'target_type' => 'internal',
                                'is_active' => true,
                                'match_type' => 'exact',
                                'created_by' => Auth::id(),
                            ]);

                            $record->update([
                                'status' => 'redirected',
                                'redirect_id' => $redirect->id,
                            ]);

                            Notification::make()
                                ->title('Přesměrování vytvořeno')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (NotFoundLog $record) => $record->status !== 'redirected'),

                    Action::make('ignore')
                        ->label('Ignorovat')
                        ->icon(new HtmlString('<i class="fa-light fa-eye-slash"></i>'))
                        ->color('gray')
                        ->action(function (NotFoundLog $record) {
                            $record->update([
                                'status' => 'ignored',
                                'is_ignored' => true,
                            ]);
                        })
                        ->visible(fn (NotFoundLog $record) => $record->status === 'pending'),

                    Action::make('restore')
                        ->label('Obnovit')
                        ->icon(new HtmlString('<i class="fa-light fa-arrow-rotate-left"></i>'))
                        ->action(function (NotFoundLog $record) {
                            $record->update([
                                'status' => 'pending',
                                'is_ignored' => false,
                            ]);
                        })
                        ->visible(fn (NotFoundLog $record) => $record->status !== 'pending'),

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('hits_count', 'desc');
    }

    /**
     * Jednoduchý našeptávač cílové URL.
     */
    protected static function suggestTarget(string $url): ?string
    {
        return \App\Support\RedirectSuggester::suggest($url);
    }
}
