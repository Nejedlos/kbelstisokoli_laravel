<?php

namespace App\Filament\Pages;

use App\Actions\Season\RenewSeasonAction;
use App\Models\FinancialTariff;
use App\Models\Season;
use App\Models\User;
use App\Models\UserSeasonConfig;
use App\Support\FilamentIcon;
use App\Support\Icons\AppIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class SeasonRenewal extends Page implements HasForms
{
    use InteractsWithFormActions;
    use InteractsWithForms;

    public static function getNavigationIcon(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return FilamentIcon::get(AppIcon::REFRESH);
    }

    protected string $view = 'filament.pages.season-renewal';

    public static function canAccess(): bool
    {
        return auth()->user()->can('manage_advanced_settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.system');
    }

    public static function getNavigationSort(): ?int
    {
        return 100;
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.season_renewal.navigation_label');
    }

    public function getTitle(): string
    {
        return __('admin.season_renewal.title');
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
                Section::make(__('admin.season_renewal.sections.general'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('season_id')
                                    ->label(__('admin.season_renewal.fields.season_id'))
                                    ->options(Season::all()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->helperText(__('admin.season_renewal.fields.season_id_help'))
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->loadFromPreviousSeason(notify: false)),

                                Select::make('source_season_id')
                                    ->label(__('admin.season_renewal.fields.source_season_id'))
                                    ->options(Season::all()->pluck('name', 'id'))
                                    ->placeholder(__('admin.season_renewal.fields.source_season_placeholder'))
                                    ->dehydrated(false)
                                    ->live()
                                    ->hintAction(
                                        Action::make('load_specific')
                                            ->label(__('admin.season_renewal.actions.load_specific'))
                                            ->icon(FilamentIcon::get(AppIcon::DOWNLOAD))
                                            ->color('primary')
                                            ->requiresConfirmation()
                                            ->modalHeading(__('admin.season_renewal.modals.load_specific_title'))
                                            ->modalDescription(__('admin.season_renewal.modals.load_specific_desc'))
                                            ->action(fn ($get) => $this->loadFromSeason($get('source_season_id')))
                                            ->visible(fn ($get) => filled($get('source_season_id')))
                                    ),
                            ]),
                    ]),

                Section::make(__('admin.season_renewal.sections.configs'))
                    ->description(__('admin.season_renewal.sections.configs_desc'))
                    ->schema([
                        Placeholder::make('configs_empty_state')
                            ->label('')
                            ->hidden(fn ($get) => filled($get('configs')))
                            ->content(new HtmlString('
                                <div class="flex flex-col items-center justify-center p-6 text-center bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 dark:bg-white/5 dark:border-white/10">
                                    <i class="fa-light fa-users-slash text-4xl text-gray-400 mb-3"></i>
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white">'.__('admin.season_renewal.empty_state.title').'</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto mt-1">'.__('admin.season_renewal.empty_state.desc').'</p>
                                </div>
                            ')),

                        Repeater::make('configs')
                            ->label(__('admin.season_renewal.fields.configs'))
                            ->schema([
                                Select::make('user_id')
                                    ->label(__('admin.season_renewal.fields.user_id'))
                                    ->options(User::active()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),

                                Select::make('financial_tariff_id')
                                    ->label(__('admin.season_renewal.fields.financial_tariff_id'))
                                    ->options(FinancialTariff::all()->pluck('name', 'id'))
                                    ->required(),

                                TextInput::make('opening_balance')
                                    ->label(__('admin.season_renewal.fields.opening_balance'))
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Kč'),

                                Toggle::make('track_attendance')
                                    ->label(__('admin.season_renewal.fields.track_attendance'))
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->itemLabel(fn ($state): ?string => isset($state['user_id']) ? User::find($state['user_id'])?->name : null)
                            ->addActionLabel(__('admin.season_renewal.fields.add_member'))
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
                ->label(__('admin.season_renewal.actions.load_from_previous'))
                ->color('gray')
                ->icon(FilamentIcon::get(AppIcon::DOWNLOAD))
                ->requiresConfirmation()
                ->modalHeading(__('admin.season_renewal.modals.load_from_previous_title'))
                ->modalDescription(__('admin.season_renewal.modals.load_from_previous_desc'))
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

        if (! $sourceSeason) {
            if ($notify) {
                Notification::make()
                    ->title(__('admin.season_renewal.notifications.prev_not_found'))
                    ->warning()
                    ->send();
            }

            return;
        }

        $this->loadFromSeason($sourceSeason->id, $notify);
    }

    public function loadFromSeason(?int $seasonId, bool $notify = true): void
    {
        if (! $seasonId) {
            return;
        }

        $sourceSeason = Season::find($seasonId);

        if (! $sourceSeason) {
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
                ->title(__('admin.season_renewal.notifications.data_loaded'))
                ->body(__('admin.season_renewal.notifications.data_loaded_body', [
                    'count' => count($configs),
                    'name' => $sourceSeason->name,
                ]))
                ->success()
                ->send();
        }
    }

    public function create(RenewSeasonAction $renewSeasonAction): void
    {
        $formData = $this->form->getState();
        $seasonId = $formData['season_id'];
        $sourceSeasonId = $this->data['source_season_id'] ?? null;

        try {
            $result = $renewSeasonAction->execute($seasonId, $sourceSeasonId);

            Notification::make()
                ->title(__('admin.season_renewal.notifications.success'))
                ->body(__('admin.season_renewal.notifications.success_body', [
                    'created' => $result['created'],
                    'updated' => $result['updated'],
                ]))
                ->success()
                ->persistent()
                ->send();

            $this->redirect(Dashboard::getUrl());

        } catch (\Exception $e) {
            Notification::make()
                ->title(__('admin.season_renewal.notifications.error'))
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
