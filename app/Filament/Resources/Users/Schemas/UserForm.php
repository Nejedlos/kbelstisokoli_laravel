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
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
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
                static::getSummaryCard()
                    ->columnSpanFull(),

                Tabs::make('User Management')
                    ->tabs([
                        static::getOverviewTab(),
                        static::getPersonalTab(),
                        static::getClubTab(),
                        static::getPlayerTab(),
                        static::getSecurityTab(),
                        static::getAdminTab(),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    protected static function getSummaryCard(): Section
    {
        return Section::make()
            ->schema([
                Grid::make([
                    'default' => 1,
                    'md' => 12,
                ])
                ->schema([
                    SpatieMediaLibraryFileUpload::make('avatar')
                        ->collection('avatar')
                        ->disk(config('filesystems.uploads.disk'))
                        ->avatar()
                        ->alignLeft()
                        ->hiddenLabel()
                        ->imageEditor()
                        ->getUploadedFileNameForStorageUsing(function ($file, $get) {
                            $name = $get('last_name') ? \Illuminate\Support\Str::slug($get('last_name')) : 'avatar';
                            return $name . '-' . time() . '.' . $file->getClientOriginalExtension();
                        })
                        ->columnSpan([
                            'default' => 1,
                            'md' => 2,
                        ]),

                    Placeholder::make('member_info')
                        ->hiddenLabel()
                        ->content(fn ($record) => new HtmlString("
                            <div class='py-2'>
                                <h2 class='text-3xl font-black text-gray-900 dark:text-white tracking-tight leading-tight'>" . ($record?->name ?? 'Nový člen klubu') . "</h2>
                                " . ($record ? "
                                <div class='flex flex-wrap gap-1.5 mt-2'>
                                    " . ($record->roles->map(fn($role) => "
                                        <span class='px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-primary-50 text-primary-700 border border-primary-200 dark:bg-primary-950 dark:text-primary-300 dark:border-primary-800 rounded-lg'>
                                            {$role->name}
                                        </span>
                                    ")->implode('')) . "
                                </div>

                                <div class='mt-6 flex flex-wrap gap-x-12 gap-y-4'>
                                    <div class='flex flex-col'>
                                        <span class='text-gray-500 dark:text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1'>" . __('user.fields.club_member_id') . "</span>
                                        <span class='font-mono font-black text-gray-900 dark:text-gray-100 text-xl'>{$record->club_member_id}</span>
                                    </div>
                                    <div class='flex flex-col'>
                                        <span class='text-gray-500 dark:text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1'>" . __('user.fields.payment_vs') . "</span>
                                        <span class='font-mono font-black text-primary-600 dark:text-primary-400 text-xl'>{$record->payment_vs}</span>
                                    </div>
                                    <div class='flex flex-col'>
                                        <span class='text-gray-500 dark:text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1'>Status</span>
                                        <span class='font-black text-gray-900 dark:text-white text-xl uppercase'>" . ($record?->membership_status?->getLabel() ?? '-') . "</span>
                                    </div>
                                    <div class='flex flex-col'>
                                        <span class='text-gray-500 dark:text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1'>Účet</span>
                                        " . ($record?->is_active
                                            ? "<span class='text-success-600 dark:text-success-400 font-black flex items-center gap-1.5 text-xl uppercase'>" . \App\Support\IconHelper::render(\App\Support\IconHelper::CIRCLE_CHECK) . " Aktivní</span>"
                                            : "<span class='text-danger-600 dark:text-danger-400 font-black flex items-center gap-1.5 text-xl uppercase'>" . \App\Support\IconHelper::render(\App\Support\IconHelper::CIRCLE_XMARK) . " Neaktivní</span>") . "
                                    </div>
                                </div>
                                " : "<p class='text-gray-500 dark:text-gray-400 mt-2'>Vyplňte základní údaje pro vytvoření nového člena.</p>") . "
                            </div>
                        "))
                        ->columnSpan([
                            'default' => 1,
                            'md' => 10,
                        ]),
                ]),
            ]);
    }

    protected static function getOverviewTab(): Tabs\Tab
    {
        return Tabs\Tab::make(__('user.tabs.overview'))
            ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::VIEW))
            ->schema([
                Section::make(__('user.sections.identity'))
                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::IDENTITY))
                    ->description(__('user.sections.identity_desc'))
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
                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::PHONE))
                    ->description(__('user.sections.contact_desc'))
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('phone')
                                    ->label(__('user.fields.phone'))
                                    ->tel()
                                    ->prefix('+420')
                                    ->helperText(__('user.helpers.phone_9_digits'))
                                    ->rules(['phone:CZ'])
                                    ->dehydrateStateUsing(fn ($state) => $state ? str_replace(' ', '', $state) : $state)
                                    ->extraAlpineAttributes([
                                        'x-init' => '$el.value = $el.value.replace(/^\+420\s?/, \'\').replace(/\s/g, \'\').trim()',
                                        'x-on:input' => '$el.value = $el.value.replace(/^\+420\s?/, \'\').replace(/\s/g, \'\'); $el.dispatchEvent(new Event(\'input\'))',
                                        'x-on:blur' => '$el.value = $el.value.replace(/^\+420\s?/, \'\').replace(/\s/g, \'\').trim(); $el.dispatchEvent(new Event(\'input\'))',
                                    ]),
                                TextInput::make('phone_secondary')
                                    ->label(__('user.fields.phone_secondary'))
                                    ->tel()
                                    ->prefix('+420')
                                    ->helperText(__('user.helpers.phone_9_digits'))
                                    ->rules(['phone:CZ'])
                                    ->dehydrateStateUsing(fn ($state) => $state ? str_replace(' ', '', $state) : $state)
                                    ->extraAlpineAttributes([
                                        'x-init' => '$el.value = $el.value.replace(/^\+420\s?/, \'\').replace(/\s/g, \'\').trim()',
                                        'x-on:input' => '$el.value = $el.value.replace(/^\+420\s?/, \'\').replace(/\s/g, \'\'); $el.dispatchEvent(new Event(\'input\'))',
                                        'x-on:blur' => '$el.value = $el.value.replace(/^\+420\s?/, \'\').replace(/\s/g, \'\').trim(); $el.dispatchEvent(new Event(\'input\'))',
                                    ]),
                            ]),
                    ]),
            ]);
    }

    protected static function getPersonalTab(): Tabs\Tab
    {
        return Tabs\Tab::make(__('user.tabs.personal'))
            ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::USER))
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
                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::LOCATION))
                    ->description(__('user.sections.address_desc'))
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
                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::EMERGENCY))
                    ->compact()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('emergency_contact_name')
                                    ->label(__('user.fields.emergency_contact_name')),
                                TextInput::make('emergency_contact_phone')
                                    ->label(__('user.fields.emergency_contact_phone'))
                                    ->tel()
                                    ->prefix('+420')
                                    ->helperText(__('user.helpers.phone_9_digits'))
                                    ->rules(['phone:CZ'])
                                    ->dehydrateStateUsing(fn ($state) => $state ? str_replace(' ', '', $state) : $state)
                                    ->extraAlpineAttributes([
                                        'x-init' => '$el.value = $el.value.replace(/^\+420\s?/, \'\').replace(/\s/g, \'\').trim()',
                                        'x-on:input' => '$el.value = $el.value.replace(/^\+420\s?/, \'\').replace(/\s/g, \'\'); $el.dispatchEvent(new Event(\'input\'))',
                                        'x-on:blur' => '$el.value = $el.value.replace(/^\+420\s?/, \'\').replace(/\s/g, \'\').trim(); $el.dispatchEvent(new Event(\'input\'))',
                                    ]),
                            ]),
                    ]),
            ]);
    }

    protected static function getClubTab(): Tabs\Tab
    {
        return Tabs\Tab::make(__('user.tabs.club'))
            ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::CLUB))
            ->schema([
                Section::make(__('user.sections.membership'))
                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::BADGE))
                    ->description(__('user.sections.membership_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('club_member_id')
                                    ->label(__('user.fields.club_member_id'))
                                    ->unique(ignoreRecord: true)
                                    ->suffixAction(
                                        Action::make('generate_id')
                                            ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::REFRESH))
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
                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::FINANCE_PAYMENTS))
                    ->description(__('user.sections.payments_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('payment_vs')
                                    ->label(__('user.fields.payment_vs'))
                                    ->unique(ignoreRecord: true)
                                    ->suffixAction(
                                        Action::make('generate_vs')
                                            ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::REFRESH))
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
            ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::BASKETBALL))
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
                                Placeholder::make('active_profile_info')
                                    ->hiddenLabel()
                                    ->content(fn ($record) => $record?->activePlayerProfile
                                        ? new HtmlString("<div class='bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 p-4 rounded-xl mb-4'>
                                            <div class='flex items-center gap-3'>
                                                <div class='p-2 bg-primary-100 dark:bg-primary-800 rounded-lg text-primary-600 dark:text-primary-400'>
                                                    " . \App\Support\IconHelper::render(\App\Support\IconHelper::CLOCK) . "
                                                </div>
                                                <div>
                                                    <p class='text-sm font-bold text-primary-900 dark:text-primary-100'>Aktuálně aktivní profil</p>
                                                    <p class='text-xs text-primary-700 dark:text-primary-300'>
                                                        Platnost od: <b>" . ($record->activePlayerProfile->valid_from?->format('d.m.Y') ?? 'neurčeno') . "</b>
                                                        " . ($record->activePlayerProfile->valid_to ? " do: <b>" . $record->activePlayerProfile->valid_to->format('d.m.Y') . "</b>" : " (bez omezení)") . "
                                                    </p>
                                                </div>
                                            </div>
                                          </div>")
                                        : new HtmlString("<div class='bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 p-4 rounded-xl mb-4 text-warning-800 dark:text-warning-200 text-sm'>
                                            Uživatel aktuálně nemá žádný aktivní hráčský profil.
                                          </div>")
                                    ),

                                Section::make(__('user.sections.basketball'))
                                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::BASKETBALL))
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
                                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::PHYSICAL))
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
                                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::NOTE))
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

                                Section::make(__('user.sections.player_photos'))
                                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::IMAGE))
                                    ->description(__('user.sections.player_photos_desc'))
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('player_photos')
                                            ->collection('player_photos')
                                            ->multiple()
                                            ->reorderable()
                                            ->imageEditor()
                                            ->hiddenLabel()
                                            ->disk(config('filesystems.uploads.disk'))
                                            ->helperText('Pořadí určuje primární fotografii pro soupisku – první je primární.')
                                            ->panelLayout('grid')
                                            ->responsiveImages(),
                                    ]),
                            ])
                            ->visible(fn($get) => $get('player_profile_active')),
                    ]),
            ]);
    }

    protected static function getSecurityTab(): Tabs\Tab
    {
        return Tabs\Tab::make(__('user.tabs.security'))
            ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::SHIELD_CHECK))
            ->schema([
                Section::make(__('user.sections.security_password'))
                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::SECURITY))
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
                                    ->default(true)
                                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord),
                                Placeholder::make('is_active_status')
                                    ->label(__('user.fields.is_active'))
                                    ->content(fn ($record) => $record?->is_active
                                        ? new HtmlString('<div class="flex items-center gap-2 text-success-600 dark:text-success-400 font-bold uppercase">' . \App\Support\IconHelper::render(\App\Support\IconHelper::CIRCLE_CHECK) . ' Aktivní účet</div>')
                                        : new HtmlString('<div class="flex items-center gap-2 text-danger-600 dark:text-danger-400 font-bold uppercase">' . \App\Support\IconHelper::render(\App\Support\IconHelper::CIRCLE_XMARK) . ' Neaktivní účet</div>')
                                    )
                                    ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord)
                                    ->hintAction(
                                        Action::make('toggle_active_record')
                                            ->label(fn ($record) => $record?->is_active ? 'Deaktivovat' : 'Aktivovat')
                                            ->icon(fn ($record) => $record?->is_active ? \App\Support\IconHelper::get(\App\Support\IconHelper::DEACTIVATE) : \App\Support\IconHelper::get(\App\Support\IconHelper::ACTIVATE))
                                            ->color(fn ($record) => $record?->is_active ? 'danger' : 'success')
                                            ->requiresConfirmation()
                                            ->modalHeading(fn ($record) => $record?->is_active ? 'Deaktivovat účet?' : 'Aktivovat účet?')
                                            ->modalDescription('Změna stavu aktivity účtu má okamžitý vliv na možnost uživatele přihlásit se do systému.')
                                            ->action(function ($record) {
                                                $record->is_active = !$record->is_active;
                                                $record->save();

                                                Notification::make()
                                                    ->title($record->is_active ? 'Účet aktivován' : 'Účet deaktivován')
                                                    ->success()
                                                    ->send();
                                            })
                                    ),
                            ]),
                    ]),

                Section::make(__('user.sections.security_2fa'))
                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::PERMISSIONS))
                    ->description('Zabezpečení účtu pomocí druhého faktoru.')
                    ->aside()
                    ->schema([
                        Placeholder::make('2fa_status_detailed')
                            ->label('Aktuální stav')
                            ->content(fn ($record) => match (true) {
                                !$record?->two_factor_secret => new HtmlString('<div class="flex items-center gap-2 text-danger-600 dark:text-danger-400 font-medium">' . \App\Support\IconHelper::render(\App\Support\IconHelper::CIRCLE_XMARK) . ' Neaktivní</div>'),
                                !$record->two_factor_confirmed_at => new HtmlString('<div class="flex items-center gap-2 text-warning-600 dark:text-warning-400 font-medium">' . \App\Support\IconHelper::render(\App\Support\IconHelper::INFO) . ' Čeká na potvrzení</div>'),
                                default => new HtmlString('<div class="flex items-center gap-2 text-success-600 dark:text-success-400 font-medium">' . \App\Support\IconHelper::render(\App\Support\IconHelper::CIRCLE_CHECK) . ' Aktivní a ověřeno</div>'),
                            }),
                        Placeholder::make('2fa_confirmed_at')
                            ->label('Datum aktivace')
                            ->content(fn ($record) => $record?->two_factor_confirmed_at?->format('d.m.Y H:i') ?? '-')
                            ->visible(fn ($record) => $record?->two_factor_confirmed_at !== null),
                    ])
                    ->headerActions([
                        Action::make('disable_2fa')
                            ->label(__('user.actions.reset_2fa'))
                            ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::TRASH))
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
            ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::USER_GEAR))
            ->schema([
                Section::make(__('user.sections.internal'))
                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::GEARS))
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
                    ->icon(\App\Support\IconHelper::get(\App\Support\IconHelper::AUDIT))
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
