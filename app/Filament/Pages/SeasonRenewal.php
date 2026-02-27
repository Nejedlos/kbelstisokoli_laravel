<?php

namespace App\Filament\Pages;

use App\Models\FinancialTariff;
use App\Models\Season;
use App\Models\User;
use App\Models\UserSeasonConfig;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class SeasonRenewal extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;
    public static function getNavigationIcon(): ?string
    {
        return 'fal-arrows-rotate';
    }

    protected string $view = 'filament.pages.season-renewal';

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    public function getTitle(): string
    {
        return 'Hromadná inicializace sezóny';
    }

    protected static ?string $slug = 'season-renewal';

    public ?array $data = [];

    public function mount(): void
    {
        $expectedSeasonName = Season::getExpectedCurrentSeasonName();
        $targetSeason = Season::where('name', $expectedSeasonName)->first();

        $this->data = [
            'season_id' => $targetSeason?->id,
            'configs' => [],
            'source_season_id' => null,
        ];

        // Defaultně načteme z předchozí sezóny
        $this->loadFromPreviousSeason(notify: false);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní nastavení')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('season_id')
                                    ->label('Cílová sezóna')
                                    ->options(Season::all()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->helperText('Vyberte sezónu, pro kterou chcete vytvořit konfigurace.')
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->loadFromPreviousSeason(notify: false)),

                                Select::make('source_season_id')
                                    ->label('Zdrojová sezóna (pro načtení)')
                                    ->options(Season::all()->pluck('name', 'id'))
                                    ->placeholder('Vyberte pro načtení dat...')
                                    ->dehydrated(false)
                                    ->live()
                                    ->hintAction(
                                        Action::make('load_specific')
                                            ->label('Načíst data ze sezóny')
                                            ->icon('fal-download')
                                            ->color('primary')
                                            ->requiresConfirmation()
                                            ->modalHeading('Načíst data ze zvolené sezóny?')
                                            ->modalDescription('Tato akce nahradí aktuální seznam konfigurací daty z vybrané sezóny.')
                                            ->action(fn ($get) => $this->loadFromSeason($get('source_season_id')))
                                            ->visible(fn ($get) => filled($get('source_season_id')))
                                    ),
                            ]),
                    ]),

                Section::make('Konfigurace členů')
                    ->description('Zde můžete hromadně nastavit parametry pro jednotlivé členy.')
                    ->schema([
                        Placeholder::make('configs_empty_state')
                            ->label('')
                            ->hidden(fn ($get) => filled($get('configs')))
                            ->content(new HtmlString('
                                <div class="flex flex-col items-center justify-center p-6 text-center bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 dark:bg-white/5 dark:border-white/10">
                                    <i class="fa-light fa-users-slash text-4xl text-gray-400 mb-3"></i>
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white">Žádní členové nebyli načteni</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto mt-1">Pro tuto sezónu zatím neexistují žádné konfigurace. Vyberte zdrojovou sezónu výše a klikněte na "Načíst data", nebo přidejte členy ručně tlačítkem níže.</p>
                                </div>
                            ')),

                        Repeater::make('configs')
                            ->label('Seznam členů')
                            ->schema([
                                Select::make('user_id')
                                    ->label('Člen')
                                    ->options(User::active()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),

                                Select::make('financial_tariff_id')
                                    ->label('Tarif')
                                    ->options(FinancialTariff::all()->pluck('name', 'id'))
                                    ->required(),

                                TextInput::make('opening_balance')
                                    ->label('Počáteční zůstatek')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Kč'),

                                Toggle::make('track_attendance')
                                    ->label('Hlídat docházku')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->itemLabel(fn ($state): ?string => isset($state['user_id']) ? User::find($state['user_id'])?->name : null)
                            ->addActionLabel('Přidat dalšího člena')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->columns(4),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('load_from_previous')
                ->label('Načíst z předchozí sezóny')
                ->color('gray')
                ->icon('fal-download')
                ->requiresConfirmation()
                ->modalHeading('Načíst data?')
                ->modalDescription('Tato akce nahradí aktuální seznam konfigurací daty z předchozí sezóny.')
                ->action(fn () => $this->loadFromPreviousSeason()),
        ];
    }

    public function loadFromPreviousSeason(bool $notify = true): void
    {
        $targetSeasonId = $this->data['season_id'] ?? null;
        $targetSeason = $targetSeasonId ? Season::find($targetSeasonId) : null;

        if ($targetSeason) {
            $prevSeasonName = Season::getPreviousSeasonNameFrom($targetSeason->name);
        } else {
            $prevSeasonName = Season::getPreviousSeasonName();
        }

        $altName = str_replace('/', '-', $prevSeasonName);
        $sourceSeason = Season::where('name', $prevSeasonName)
            ->orWhere('name', $altName)
            ->first();

        if (!$sourceSeason) {
            if ($notify) {
                Notification::make()
                    ->title('Předchozí sezóna nebyla nalezena')
                    ->warning()
                    ->send();
            }
            return;
        }

        $this->loadFromSeason($sourceSeason->id, $notify);
    }

    public function loadFromSeason(?int $seasonId, bool $notify = true): void
    {
        if (!$seasonId) {
            return;
        }

        $sourceSeason = Season::find($seasonId);

        if (!$sourceSeason) {
            return;
        }

        $configs = UserSeasonConfig::where('season_id', $sourceSeason->id)
            ->get()
            ->map(fn ($c) => [
                'user_id' => $c->user_id,
                'financial_tariff_id' => $c->financial_tariff_id,
                'opening_balance' => 0,
                'track_attendance' => $c->track_attendance,
            ])
            ->toArray();

        $this->data['configs'] = $configs;
        $this->data['source_season_id'] = $sourceSeason->id;

        if ($notify) {
            Notification::make()
                ->title('Data byla načtena')
                ->body("Bylo načteno " . count($configs) . " záznamů ze sezóny {$sourceSeason->name}.")
                ->success()
                ->send();
        }
    }

    public function create(): void
    {
        $formData = $this->form->getState();
        $seasonId = $formData['season_id'];
        $configs = $formData['configs'];

        if (empty($configs)) {
            Notification::make()
                ->title('Chyba')
                ->body('Seznam konfigurací je prázdný.')
                ->danger()
                ->send();
            return;
        }

        DB::beginTransaction();

        try {
            $created = 0;
            $updated = 0;

            foreach ($configs as $configData) {
                $record = UserSeasonConfig::updateOrCreate(
                    [
                        'user_id' => $configData['user_id'],
                        'season_id' => $seasonId,
                    ],
                    [
                        'financial_tariff_id' => $configData['financial_tariff_id'],
                        'opening_balance' => $configData['opening_balance'] ?? 0,
                        'track_attendance' => $configData['track_attendance'],
                    ]
                );

                if ($record->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }

            DB::commit();

            Notification::make()
                ->title('Uloženo')
                ->body("Vytvořeno {$created} a aktualizováno {$updated} konfigurací.")
                ->success()
                ->persistent()
                ->send();

            $this->redirect(Dashboard::getUrl());

        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Chyba při ukládání')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->canAccessAdmin() ?? false;
    }
}
