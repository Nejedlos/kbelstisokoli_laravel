<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Gender;
use App\Enums\MembershipStatus;
use App\Enums\MembershipType;
use App\Enums\PaymentMethod;
use App\Enums\BasketballPosition;
use App\Enums\DominantHand;
use App\Enums\JerseySize;
use App\Services\ClubIdentifierService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                static::getSummaryCard(),
                            ])
                            ->columnSpan(1),

                        Tabs::make('User Management')
                            ->tabs([
                                static::getOverviewTab(),
                                static::getPersonalTab(),
                                static::getClubTab(),
                                static::getPlayerTab(),
                                static::getSecurityTab(),
                                static::getAdminTab(),
                            ])
                            ->columnSpan(2)
                            ->persistTabInQueryString(),
                    ]),
            ]);
    }

    protected static function getSummaryCard(): Section
    {
        return Section::make()
            ->schema([
                SpatieMediaLibraryFileUpload::make('avatar')
                    ->collection('avatars')
                    ->avatar()
                    ->alignCenter()
                    ->hiddenLabel(),

                Placeholder::make('member_info')
                    ->hiddenLabel()
                    ->content(fn ($record) => $record ? new HtmlString("
                        <div class='text-center'>
                            <h2 class='text-xl font-bold'>{$record->name}</h2>
                            <div class='flex justify-center gap-1 mt-1'>
                                " . ($record->roles->map(fn($role) => "<span class='px-2 py-0.5 text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300 rounded-full'>{$role->name}</span>")->implode('')) . "
                            </div>
                            <div class='mt-4 flex flex-col gap-2 text-sm text-gray-600 dark:text-gray-400'>
                                <div class='flex items-center justify-between'>
                                    <span>" . __('user.fields.club_member_id') . ":</span>
                                    <span class='font-mono font-bold text-gray-900 dark:text-gray-100'>{$record->club_member_id}</span>
                                </div>
                                <div class='flex items-center justify-between'>
                                    <span>" . __('user.fields.payment_vs') . ":</span>
                                    <span class='font-mono font-bold text-primary-600 dark:text-primary-400'>{$record->payment_vs}</span>
                                </div>
                            </div>
                        </div>
                    ") : ''),

                Grid::make(2)
                    ->schema([
                        Placeholder::make('status_badge')
                            ->label('Status')
                            ->content(fn($record) => $record?->membership_status?->getLabel() ?? '-'),
                        Placeholder::make('active_badge')
                            ->label('Účet')
                            ->content(fn($record) => $record?->is_active
                                ? new HtmlString('<span class="text-success-600 font-bold">Aktivní</span>')
                                : new HtmlString('<span class="text-danger-600 font-bold">Neaktivní</span>')),
                    ]),
            ]);
    }

    protected static function getOverviewTab(): Tabs\Tab
    {
        return Tabs\Tab::make(__('user.tabs.overview'))
            ->icon('fa-light fa-eye')
            ->schema([
                Section::make(__('user.sections.identity'))
                    ->description(__('user.sections.identity_desc'))
                    ->icon('fa-light fa-id-card')
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('first_name')
                                    ->label(__('user.fields.first_name'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('last_name')
                                    ->label(__('user.fields.last_name'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('display_name')
                                    ->label(__('user.fields.display_name'))
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label(__('user.fields.email'))
                                    ->email()
                                    ->required()
                                    ->unique('users', 'email', ignoreRecord: true),
                            ]),
                    ]),

                Section::make(__('user.sections.contact'))
                    ->description(__('user.sections.contact_desc'))
                    ->icon('fa-light fa-phone')
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('phone')
                                    ->label(__('user.fields.phone'))
                                    ->tel(),
                                TextInput::make('phone_secondary')
                                    ->label(__('user.fields.phone_secondary'))
                                    ->tel(),
                            ]),
                    ]),
            ]);
    }

    protected static function getPersonalTab(): Tabs\Tab
    {
        return Tabs\Tab::make(__('user.tabs.personal'))
            ->icon('fa-light fa-user')
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('date_of_birth')
                                    ->label(__('user.fields.date_of_birth'))
                                    ->native(false)
                                    ->displayFormat('d.m.Y'),
                                Select::make('gender')
                                    ->label(__('user.fields.gender'))
                                    ->options(Gender::class),
                                Select::make('preferred_locale')
                                    ->label(__('user.fields.preferred_locale'))
                                    ->options([
                                        'cs' => 'Čeština',
                                        'en' => 'English',
                                    ])
                                    ->default('cs')
                                    ->required(),
                                TextInput::make('nationality')
                                    ->label(__('user.fields.nationality')),
                            ]),
                    ]),

                Section::make(__('user.sections.address'))
                    ->description(__('user.sections.address_desc'))
                    ->icon('fa-light fa-location-dot')
                    ->compact()
                    ->schema([
                        TextInput::make('address_street')
                            ->label(__('user.fields.address_street'))
                            ->maxLength(255),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('address_city')
                                    ->label(__('user.fields.address_city'))
                                    ->maxLength(255),
                                TextInput::make('address_zip')
                                    ->label(__('user.fields.address_zip'))
                                    ->maxLength(255),
                                TextInput::make('address_country')
                                    ->label(__('user.fields.address_country'))
                                    ->default('CZ')
                                    ->maxLength(255),
                            ]),
                    ]),

                Section::make('Nouzový kontakt')
                    ->icon('fa-light fa-truck-medical')
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('emergency_contact_name')
                                    ->label(__('user.fields.emergency_contact_name')),
                                TextInput::make('emergency_contact_phone')
                                    ->label(__('user.fields.emergency_contact_phone'))
                                    ->tel(),
                            ]),
                    ]),
            ]);
    }

    protected static function getClubTab(): Tabs\Tab
    {
        return Tabs\Tab::make(__('user.tabs.club'))
            ->icon('fa-light fa-shield-halved')
            ->schema([
                Section::make(__('user.sections.membership'))
                    ->description(__('user.sections.membership_desc'))
                    ->icon('fa-light fa-id-badge')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('club_member_id')
                                    ->label(__('user.fields.club_member_id'))
                                    ->unique(ignoreRecord: true)
                                    ->suffixAction(
                                        Action::make('generate_id')
                                            ->icon('fa-light fa-arrows-rotate')
                                            ->action(function ($set) {
                                                $set('club_member_id', app(ClubIdentifierService::class)->generateClubMemberId());
                                            })
                                    ),
                                Select::make('membership_status')
                                    ->label(__('user.fields.membership_status'))
                                    ->options(MembershipStatus::class)
                                    ->required(),
                                Select::make('membership_type')
                                    ->label(__('user.fields.membership_type'))
                                    ->options(MembershipType::class),
                                Grid::make(2)
                                    ->schema([
                                        DatePicker::make('membership_started_at')
                                            ->label(__('user.fields.membership_started_at'))
                                            ->native(false),
                                        DatePicker::make('membership_ended_at')
                                            ->label(__('user.fields.membership_ended_at'))
                                            ->native(false)
                                            ->rule('after_or_equal:membership_started_at'),
                                    ]),
                            ]),
                    ]),

                Section::make(__('user.sections.payments'))
                    ->description(__('user.sections.payments_desc'))
                    ->icon('fa-light fa-money-bill-transfer')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('payment_vs')
                                    ->label(__('user.fields.payment_vs'))
                                    ->unique(ignoreRecord: true)
                                    ->suffixAction(
                                        Action::make('generate_vs')
                                            ->icon('fa-light fa-arrows-rotate')
                                            ->action(function ($set) {
                                                $set('payment_vs', app(ClubIdentifierService::class)->generatePaymentVs());
                                            })
                                    ),
                                Toggle::make('finance_ok')
                                    ->label(__('user.fields.finance_ok'))
                                    ->onColor('success'),
                                Select::make('payment_method')
                                    ->label(__('user.fields.payment_method'))
                                    ->options(PaymentMethod::class),
                                Textarea::make('payment_note')
                                    ->label(__('user.fields.payment_note'))
                                    ->rows(2),
                            ]),
                    ]),
            ]);
    }

    protected static function getPlayerTab(): Tabs\Tab
    {
        return Tabs\Tab::make(__('user.tabs.player'))
            ->icon('fa-light fa-basketball')
            ->schema([
                Grid::make(1)
                    ->schema([
                        Toggle::make('has_player_profile')
                            ->label('Má aktivní hráčský profil')
                            ->helperText('Pokud je zapnuto, uživatel má herní statistiky a profil v klubu.')
                            ->live()
                            ->statePath('player_profile_active') // This will need handling in the Page
                            ->dehydrated(false)
                            ->afterStateHydrated(fn ($state, $record, $set) => $set('player_profile_active', $record?->playerProfile !== null)),

                        Grid::make(1)
                            ->schema([
                                Section::make(__('user.sections.basketball'))
                                    ->description(__('user.sections.basketball_desc'))
                                    ->relationship('playerProfile')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('jersey_number')
                                                    ->label(__('user.fields.jersey_number'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->maxValue(99),
                                                TextInput::make('preferred_jersey_number')
                                                    ->label(__('user.fields.preferred_jersey_number'))
                                                    ->numeric(),
                                                Select::make('position')
                                                    ->label(__('user.fields.position'))
                                                    ->options(BasketballPosition::class),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('dominant_hand')
                                                    ->label(__('user.fields.dominant_hand'))
                                                    ->options(DominantHand::class),
                                                TextInput::make('license_number')
                                                    ->label(__('user.fields.license_number')),
                                            ]),
                                        Select::make('primary_team_id')
                                            ->label(__('user.fields.primary_team'))
                                            ->relationship('primaryTeam', 'name'),
                                        DatePicker::make('joined_team_at')
                                            ->label(__('user.fields.joined_team_at'))
                                            ->native(false),
                                    ]),

                                Section::make(__('user.sections.physical'))
                                    ->description(__('user.sections.physical_desc'))
                                    ->relationship('playerProfile')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('height_cm')
                                                    ->label(__('user.fields.height_cm'))
                                                    ->numeric()
                                                    ->suffix('cm'),
                                                TextInput::make('weight_kg')
                                                    ->label(__('user.fields.weight_kg'))
                                                    ->numeric()
                                                    ->suffix('kg'),
                                                Select::make('jersey_size')
                                                    ->label(__('user.fields.jersey_size'))
                                                    ->options(JerseySize::class),
                                                Select::make('shorts_size')
                                                    ->label(__('user.fields.shorts_size'))
                                                    ->options(JerseySize::class),
                                            ]),
                                    ]),

                                Section::make(__('user.sections.internal'))
                                    ->description(__('user.sections.internal_desc'))
                                    ->relationship('playerProfile')
                                    ->collapsed()
                                    ->schema([
                                        Textarea::make('medical_note')
                                            ->label(__('user.fields.medical_note'))
                                            ->rows(3),
                                        Textarea::make('coach_note')
                                            ->label(__('user.fields.coach_note'))
                                            ->rows(3),
                                    ]),
                            ])
                            ->visible(fn($get) => $get('player_profile_active')),
                    ]),
            ]);
    }

    protected static function getSecurityTab(): Tabs\Tab
    {
        return Tabs\Tab::make(__('user.tabs.security'))
            ->icon('fa-light fa-shield-check')
            ->schema([
                Section::make(__('user.sections.security_password'))
                    ->description('Správa přístupových údajů.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('password')
                                    ->label(__('user.fields.password'))
                                    ->password()
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                    ->maxLength(255),
                                Toggle::make('is_active')
                                    ->label(__('user.fields.is_active'))
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->default(true),
                            ]),
                    ]),

                Section::make(__('user.sections.security_2fa'))
                    ->icon('fa-light fa-key')
                    ->description('Zabezpečení účtu pomocí druhého faktoru.')
                    ->aside()
                    ->schema([
                        Placeholder::make('2fa_status_detailed')
                            ->label('Aktuální stav')
                            ->content(fn ($record) => match (true) {
                                !$record?->two_factor_secret => new HtmlString('<div class="flex items-center gap-2 text-danger-600 dark:text-danger-400 font-medium"><i class="fa-light fa-circle-xmark"></i> Neaktivní</div>'),
                                !$record->two_factor_confirmed_at => new HtmlString('<div class="flex items-center gap-2 text-warning-600 dark:text-warning-400 font-medium"><i class="fa-light fa-circle-exclamation"></i> Čeká na potvrzení</div>'),
                                default => new HtmlString('<div class="flex items-center gap-2 text-success-600 dark:text-success-400 font-medium"><i class="fa-light fa-circle-check"></i> Aktivní a ověřeno</div>'),
                            }),
                        Placeholder::make('2fa_confirmed_at')
                            ->label('Datum aktivace')
                            ->content(fn ($record) => $record?->two_factor_confirmed_at?->format('d.m.Y H:i') ?? '-')
                            ->visible(fn ($record) => $record?->two_factor_confirmed_at !== null),
                    ])
                    ->headerActions([
                        Action::make('disable_2fa')
                            ->label(__('user.actions.reset_2fa'))
                            ->icon('fa-light fa-trash-can')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->action(function ($record) {
                                $record->forceFill([
                                    'two_factor_secret' => null,
                                    'two_factor_recovery_codes' => null,
                                    'two_factor_confirmed_at' => null,
                                ])->save();

                                Notification::make()
                                    ->title('2FA bylo úspěšně vypnuto')
                                    ->success()
                                    ->send();
                            })
                            ->visible(fn ($record) => $record?->two_factor_secret !== null)
                            ->authorize(fn ($record) => auth()->user()?->can('manage_users') || ($record && $record->id === auth()->id())),
                    ]),
            ]);
    }

    protected static function getAdminTab(): Tabs\Tab
    {
        return Tabs\Tab::make(__('user.tabs.admin'))
            ->icon('fa-light fa-gears')
            ->schema([
                Section::make(__('user.sections.internal'))
                    ->schema([
                        Select::make('roles')
                            ->label('Role uživatele')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        Textarea::make('admin_note')
                            ->label(__('user.fields.admin_note'))
                            ->rows(5),
                    ]),

                Section::make('Audit')
                    ->compact()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label('Vytvořeno')
                                    ->content(fn($record) => $record?->created_at?->format('d.m.Y H:i') ?? '-'),
                                Placeholder::make('updated_at')
                                    ->label('Upraveno')
                                    ->content(fn($record) => $record?->updated_at?->format('d.m.Y H:i') ?? '-'),
                                Placeholder::make('last_login_at')
                                    ->label('Poslední přihlášení')
                                    ->content(fn($record) => $record?->last_login_at?->format('d.m.Y H:i') ?? '-'),
                            ]),
                    ]),
            ]);
    }
}
