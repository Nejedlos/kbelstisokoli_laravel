<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\BrandingService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BrandingSettings extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.pages.branding');
    }

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::BRANDING);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.admin_tools');
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('admin.navigation.pages.branding');
    }

    protected string $view = 'filament.pages.branding-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $dbData = Setting::pluck('value', 'key')->toArray();
        $configDefaults = [
            'club_name' => config('branding.club_name'),
            'club_short_name' => config('branding.club_short_name'),
            'slogan' => config('branding.slogan'),
            'footer_text' => config('branding.footer_text'),
        ];

        $this->data = array_merge($configDefaults, $dbData);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('admin/branding-settings.sections.theme'))
                    ->description(__('admin/branding-settings.sections.theme_desc'))
                    ->schema([
                        Select::make('theme_preset')
                            ->label(__('admin/branding-settings.fields.theme_preset'))
                            ->options(
                                collect(config('branding.themes'))->mapWithKeys(fn ($item, $key) => [$key => $item['label']])->toArray()
                            )
                            ->default(config('branding.default_theme'))
                            ->required(),

                        Grid::make(3)
                            ->schema([
                                Select::make('header_variant')
                                    ->label(__('admin/branding-settings.fields.header_variant'))
                                    ->options([
                                        'light' => __('admin/branding-settings.options.header.light'),
                                        'dark' => __('admin/branding-settings.options.header.dark'),
                                        'sticky' => __('admin/branding-settings.options.header.sticky'),
                                    ])
                                    ->default('light'),
                                Select::make('footer_variant')
                                    ->label(__('admin/branding-settings.fields.footer_variant'))
                                    ->options([
                                        'simple' => __('admin/branding-settings.options.footer.simple'),
                                        'full' => __('admin/branding-settings.options.footer.full'),
                                    ])
                                    ->default('full'),
                                Select::make('button_radius')
                                    ->label(__('admin/branding-settings.fields.button_radius'))
                                    ->options([
                                        'none' => __('admin/branding-settings.options.radius.none'),
                                        'sm' => __('admin/branding-settings.options.radius.sm'),
                                        'md' => __('admin/branding-settings.options.radius.md'),
                                        'lg' => __('admin/branding-settings.options.radius.lg'),
                                        'full' => __('admin/branding-settings.options.radius.full'),
                                    ])
                                    ->default('md'),
                            ]),
                    ]),

                Section::make(__('admin/branding-settings.sections.identity'))
                    ->description(__('admin/branding-settings.sections.identity_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('club_name')
                                    ->label(__('admin/branding-settings.fields.club_name'))
                                    ->required(),
                                TextInput::make('club_short_name')
                                    ->label(__('admin/branding-settings.fields.club_short_name')),
                                TextInput::make('slogan')
                                    ->label(__('admin/branding-settings.fields.slogan'))
                                    ->columnSpanFull(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                FileUpload::make('logo_path')
                                    ->label(__('admin/branding-settings.fields.logo'))
                                    ->image()
                                    ->disk(config('filesystems.uploads.disk'))
                                    ->directory(trim(config('filesystems.uploads.dir', 'uploads'), '/').'/branding'),
                                FileUpload::make('alt_logo_path')
                                    ->label(__('admin/branding-settings.fields.alt_logo'))
                                    ->image()
                                    ->disk(config('filesystems.uploads.disk'))
                                    ->directory(trim(config('filesystems.uploads.dir', 'uploads'), '/').'/branding'),
                            ]),
                    ]),

                Section::make(__('admin/branding-settings.sections.contact'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('contact_email')
                                    ->label(__('admin/branding-settings.fields.email'))
                                    ->email(),
                                TextInput::make('contact_phone')
                                    ->label(__('admin/branding-settings.fields.phone')),
                                TextInput::make('contact_address')
                                    ->label(__('admin/branding-settings.fields.address')),
                                TextInput::make('admin_contact_email')
                                    ->label(__('admin/branding-settings.fields.admin_contact_email'))
                                    ->helperText(__('admin/branding-settings.fields.admin_contact_email_help'))
                                    ->email(),
                                TextInput::make('admin_contact_name')
                                    ->label(__('admin/branding-settings.fields.admin_contact_name')),
                                TextInput::make('admin_contact_phone')
                                    ->label(__('admin/branding-settings.fields.admin_contact_phone')),
                                FileUpload::make('admin_contact_photo_path')
                                    ->label(__('admin/branding-settings.fields.admin_contact_photo'))
                                    ->image()
                                    ->disk(config('filesystems.uploads.disk'))
                                    ->directory(trim(config('filesystems.uploads.dir', 'uploads'), '/').'/branding')
                                    ->helperText(__('admin/branding-settings.fields.admin_contact_photo_help')),
                            ]),
                    ]),

                Section::make(__('admin/branding-settings.sections.social'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('social_facebook')
                                    ->label(__('admin/branding-settings.fields.facebook')),
                                TextInput::make('social_instagram')
                                    ->label(__('admin/branding-settings.fields.instagram')),
                                TextInput::make('social_youtube')
                                    ->label(__('admin/branding-settings.fields.youtube')),
                            ]),
                    ]),

                Section::make(__('admin/branding-settings.sections.global_links'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('main_club_url')
                                    ->label(__('admin/branding-settings.fields.main_club_url'))
                                    ->url(),
                                TextInput::make('recruitment_url')
                                    ->label(__('admin/branding-settings.fields.recruitment_url'))
                                    ->url(),
                            ]),
                    ]),

                Section::make(__('admin/branding-settings.sections.cta'))
                    ->schema([
                        Toggle::make('cta_enabled')
                            ->label(__('admin/branding-settings.fields.cta_enabled'))
                            ->live(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('cta_label')
                                    ->label(__('admin/branding-settings.fields.cta_label'))
                                    ->required(fn ($get) => $get('cta_enabled')),
                                TextInput::make('cta_url')
                                    ->label(__('admin/branding-settings.fields.cta_url'))
                                    ->required(fn ($get) => $get('cta_enabled')),
                            ])
                            ->visible(fn ($get) => $get('cta_enabled')),
                    ]),

                Section::make(__('admin/branding-settings.sections.legal'))
                    ->schema([
                        TextInput::make('footer_text')
                            ->label(__('admin/branding-settings.fields.footer_text'))
                            ->placeholder(__('admin/branding-settings.placeholders.footer_copyright', ['year' => date('Y'), 'club' => config('branding.club_name')])),
                    ]),
                Section::make(__('admin/branding-settings.sections.maintenance'))
                    ->description(__('admin/branding-settings.sections.maintenance_desc'))
                    ->schema([
                        Toggle::make('maintenance_mode')
                            ->label(__('admin/branding-settings.fields.maintenance_mode'))
                            ->helperText(__('admin/branding-settings.fields.maintenance_mode_help'))
                            ->default(false),
                        TextInput::make('maintenance_title')
                            ->label(__('admin/branding-settings.fields.maintenance_title'))
                            ->placeholder(__('admin/branding-settings.placeholders.maintenance_title'))
                            ->default(__('admin/branding-settings.placeholders.maintenance_title')),
                        Textarea::make('maintenance_text')
                            ->label(__('admin/branding-settings.fields.maintenance_text'))
                            ->placeholder(__('admin/branding-settings.placeholders.maintenance_text'))
                            ->default(__('admin/branding-settings.placeholders.maintenance_text')),
                    ]),

                Section::make(__('admin/branding-settings.sections.seo'))
                    ->description(__('admin/branding-settings.sections.seo_desc'))
                    ->schema([
                        TextInput::make('seo_title_suffix')
                            ->label(__('admin/branding-settings.fields.seo_title_suffix'))
                            ->placeholder(__('admin/branding-settings.placeholders.seo_title_suffix'))
                            ->helperText(__('admin/branding-settings.fields.seo_title_suffix_help')),
                        Textarea::make('seo_description')
                            ->label(__('admin/branding-settings.fields.seo_description'))
                            ->helperText(__('admin/branding-settings.fields.seo_description_help')),
                        FileUpload::make('seo_og_image_path')
                            ->label(__('admin/branding-settings.fields.seo_og_image'))
                            ->image()
                            ->disk(config('filesystems.uploads.disk'))
                            ->directory(trim(config('filesystems.uploads.dir', 'uploads'), '/').'/branding')
                            ->helperText(__('admin/branding-settings.fields.seo_og_image_help')),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('seo_robots_index')
                                    ->label(__('admin/branding-settings.fields.seo_robots_index'))
                                    ->default(true),
                                Toggle::make('seo_robots_follow')
                                    ->label(__('admin/branding-settings.fields.seo_robots_follow'))
                                    ->default(true),
                            ]),
                    ]),

                Section::make(__('admin/branding-settings.sections.venue'))
                    ->description(__('admin/branding-settings.sections.venue_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('venue_name')
                                    ->label(__('admin/branding-settings.fields.venue_name')),
                                TextInput::make('match_day')
                                    ->label(__('admin/branding-settings.fields.match_day')),
                                TextInput::make('venue_street')
                                    ->label(__('admin/branding-settings.fields.venue_street')),
                                TextInput::make('venue_city')
                                    ->label(__('admin/branding-settings.fields.venue_city')),
                                TextInput::make('venue_gps')
                                    ->label(__('admin/branding-settings.fields.venue_gps')),
                                TextInput::make('venue_map_url')
                                    ->label(__('admin/branding-settings.fields.venue_map_url'))
                                    ->url(),
                            ]),
                    ]),

                Section::make(__('admin/branding-settings.sections.public_contact'))
                    ->description(__('admin/branding-settings.sections.public_contact_desc'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('contact_person')
                                    ->label(__('admin/branding-settings.fields.contact_person')),
                                TextInput::make('contact_role')
                                    ->label(__('admin/branding-settings.fields.contact_role')),
                                TextInput::make('contact_street')
                                    ->label(__('admin/branding-settings.fields.contact_street')),
                                TextInput::make('contact_city')
                                    ->label(__('admin/branding-settings.fields.contact_city')),
                                TextInput::make('contact_fax')
                                    ->label(__('admin/branding-settings.fields.contact_fax')),
                            ]),
                    ]),

                Section::make(__('admin/branding-settings.sections.economy'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('bank_account')
                                    ->label(__('admin/branding-settings.fields.bank_account'))
                                    ->helperText(__('admin/branding-settings.fields.bank_account_help'))
                                    ->placeholder('6022854477/6363'),
                                TextInput::make('bank_name')
                                    ->label(__('admin/branding-settings.fields.bank_name'))
                                    ->helperText(__('admin/branding-settings.fields.bank_name_help'))
                                    ->placeholder('Partners banka a.s.'),
                            ]),
                    ]),

                Section::make(__('Výkon a optimalizace'))
                    ->schema([
                        Select::make('perf_scenario')
                            ->label(__('Výkonnostní scénář'))
                            ->options([
                                'standard' => 'Standardní (Eager loading, Indexy)',
                                'aggressive' => 'Agresivní (+ Minifikace, Fragment cache)',
                                'ultra' => 'Ultra (+ Full-page cache, SPA režim)',
                            ])
                            ->default('standard')
                            ->live(),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('perf_full_page_cache')
                                    ->label(__('Full-page cache'))
                                    ->helperText(__('Cachuje celé stránky pro nepřihlášené uživatele (TTFB < 20ms).')),
                                Toggle::make('perf_fragment_cache')
                                    ->label(__('Fragment caching'))
                                    ->helperText(__('Cachuje části stránek jako menu a patičku.')),
                                Toggle::make('perf_html_minification')
                                    ->label(__('Minifikace HTML'))
                                    ->helperText(__('Odstraňuje přebytečné mezery z HTML kódu.')),
                                Toggle::make('perf_livewire_navigate')
                                    ->label(__('SPA režim (wire:navigate)'))
                                    ->helperText(__('Plynulé přechody mezi stránkami bez reloadu.')),
                                Toggle::make('perf_lazy_load_images')
                                    ->label(__('Lazy loading obrázků'))
                                    ->default(true),
                            ])
                            ->visible(fn ($get) => $get('perf_scenario') === 'standard'),
                    ]),
            ]);
    }

    public function save(): void
    {
        try {
            foreach ($this->data as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            app(BrandingService::class)->clearCache();

            Notification::make()
                ->title(__('admin/branding-settings.notifications.saved'))
                ->success()
                ->seconds(3)
                ->send();

            $this->dispatch('branding-saved');
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('admin/branding-settings.notifications.error'))
                ->danger()
                ->seconds(4)
                ->send();
        }
    }
}
