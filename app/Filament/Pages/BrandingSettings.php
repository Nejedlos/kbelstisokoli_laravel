<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\BrandingService;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class BrandingSettings extends Page
{
    protected static \BackedEnum|null|string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationLabel = 'Branding a vzhled';

    protected static null|string|\UnitEnum $navigationGroup = 'Nastavení';

    protected static ?string $title = 'Branding a vzhled';

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

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Téma a barvy')
                    ->description('Vyberte barevný motiv webu. Každý motiv je navržen tak, aby byl vizuálně konzistentní.')
                    ->schema([
                        Select::make('theme_preset')
                            ->label('Barevné téma (Preset)')
                            ->options(
                                collect(config('branding.themes'))->mapWithKeys(fn ($item, $key) => [$key => $item['label']])->toArray()
                            )
                            ->default(config('branding.default_theme'))
                            ->required(),

                        Grid::make(3)
                            ->schema([
                                Select::make('header_variant')
                                    ->label('Varianta hlavičky')
                                    ->options([
                                        'light' => 'Světlá',
                                        'dark' => 'Tmavá (Navy)',
                                        'sticky' => 'Sticky (při scrollu)',
                                    ])
                                    ->default('light'),
                                Select::make('footer_variant')
                                    ->label('Varianta patičky')
                                    ->options([
                                        'simple' => 'Jednoduchá',
                                        'full' => 'Kompletní s odkazy',
                                    ])
                                    ->default('full'),
                                Select::make('button_radius')
                                    ->label('Zaoblení tlačítek')
                                    ->options([
                                        'none' => 'Ostré rohy',
                                        'sm' => 'Malé',
                                        'md' => 'Střední (výchozí)',
                                        'lg' => 'Velké',
                                        'full' => 'Kulatá',
                                    ])
                                    ->default('md'),
                            ]),
                    ]),

                Section::make('Základní identita')
                    ->description('Zde nastavte názvy klubu. V textu můžete používat zástupné symboly ###TEAM_NAME### nebo ###TEAM_SHORT###, které budou automaticky nahrazeny těmito hodnotami.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('club_name')
                                    ->label('Název klubu')
                                    ->required(),
                                TextInput::make('club_short_name')
                                    ->label('Zkrácený název'),
                                TextInput::make('slogan')
                                    ->label('Slogan klubu')
                                    ->columnSpanFull(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                FileUpload::make('logo_path')
                                    ->label('Hlavní logo')
                                    ->image()
                                    ->directory('branding'),
                                FileUpload::make('alt_logo_path')
                                    ->label('Alternativní logo (světlé/tmavé)')
                                    ->image()
                                    ->directory('branding'),
                            ]),
                    ]),

                Section::make('Kontaktní údaje (Patička)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('contact_email')
                                    ->label('Kontaktní E-mail')
                                    ->email(),
                                TextInput::make('contact_phone')
                                    ->label('Kontaktní telefon'),
                                TextInput::make('contact_address')
                                    ->label('Adresa / Sídlo'),
                            ]),
                    ]),

                Section::make('Sociální sítě')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('social_facebook')
                                    ->label('Facebook URL'),
                                TextInput::make('social_instagram')
                                    ->label('Instagram URL'),
                                TextInput::make('social_youtube')
                                    ->label('YouTube URL'),
                            ]),
                    ]),

                Section::make('Výzva k akci (Globální CTA)')
                    ->schema([
                        Toggle::make('cta_enabled')
                            ->label('Povolit globální CTA tlačítko v hlavičce')
                            ->live(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('cta_label')
                                    ->label('Text tlačítka')
                                    ->required(fn ($get) => $get('cta_enabled')),
                                TextInput::make('cta_url')
                                    ->label('Odkaz (URL)')
                                    ->required(fn ($get) => $get('cta_enabled')),
                            ])
                            ->visible(fn ($get) => $get('cta_enabled')),
                    ]),

                Section::make('Právní informace')
                    ->schema([
                        TextInput::make('footer_text')
                            ->label('Copyright text (patička)')
                            ->placeholder('© ' . date('Y') . ' Kbelští sokoli. Všechna práva vyhrazena.'),
                    ]),
                Section::make('Režim přípravy (Under Construction)')
                    ->description('Aktivujte, pokud web teprve připravujete. Návštěvníkům se zobrazí stylová a vtipná stránka s basketbalovou tématikou.')
                    ->schema([
                        Toggle::make('maintenance_mode')
                            ->label('Aktivovat režim přípravy')
                            ->helperText('Pokud je zapnuto, veřejný web bude nahrazen taktickou tabulí trenéra.')
                            ->default(false),
                        TextInput::make('maintenance_title')
                            ->label('Hlavní nadpis')
                            ->placeholder('Kreslíme vítěznou taktiku')
                            ->default('Kreslíme vítěznou taktiku'),
                        Textarea::make('maintenance_text')
                            ->label('Text zprávy')
                            ->placeholder('Vzali jsme si oddechový čas, abychom do nového webu dostali všechny ty smeče a trojky...')
                            ->default('Vzali jsme si oddechový čas, abychom do nového webu dostali všechny ty smeče a trojky, které si zasloužíte. Dejte nám chvilku na střídačce, brzy se vrátíme do hry v plné sestavě!'),
                    ]),

                Section::make('Globální SEO nastavení')
                    ->description('Výchozí hodnoty pro vyhledávače a sociální sítě, které se použijí, pokud nejsou vyplněny u konkrétní stránky.')
                    ->schema([
                        TextInput::make('seo_title_suffix')
                            ->label('Přípona titulku (Title Suffix)')
                            ->placeholder(' | Název klubu')
                            ->helperText('Bude přidáno za titulek stránky (např. "O nás | Kbelští sokoli").'),
                        Textarea::make('seo_description')
                            ->label('Výchozí meta popis')
                            ->helperText('Použije se jako fallback, pokud stránka nemá vlastní popis.'),
                        FileUpload::make('seo_og_image_path')
                            ->label('Výchozí OG obrázek')
                            ->image()
                            ->directory('branding')
                            ->helperText('Obrázek, který se zobrazí při sdílení na sociálních sítích (doporučeno 1200x630px).'),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('seo_robots_index')
                                    ->label('Indexovat web (Robots Index)')
                                    ->default(true),
                                Toggle::make('seo_robots_follow')
                                    ->label('Sledovat odkazy (Robots Follow)')
                                    ->default(true),
                            ]),
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
                ->title('Nastavení uloženo')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Chyba při ukládání')
                ->danger()
                ->send();
        }
    }
}
