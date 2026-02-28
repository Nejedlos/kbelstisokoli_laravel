<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class RecaptchaSettings extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationLabel(): string
    {
        return __('admin/recaptcha-settings.navigation');
    }

    public static function getNavigationIcon(): ?string
    {
        return \App\Support\IconHelper::get(\App\Support\IconHelper::RECAPTCHA);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.groups.admin_tools');
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('admin/recaptcha-settings.title');
    }

    protected string $view = 'filament.pages.recaptcha-settings';

    public ?array $data = [];

    public function mount(): void
    {
        // Načteme všechna nastavení začínající na recaptcha_
        $settings = Setting::where('key', 'like', 'recaptcha_%')->get();
        $dbData = [];

        foreach ($settings as $setting) {
            // Získáme hodnotu bez ohledu na aktuální locale (vždy chceme stejné klíče pro všechny jazyky)
            $dbData[$setting->key] = $setting->getTranslation('value', 'cs') ?: $setting->value;
        }

        $defaults = [
            'recaptcha_enabled' => false,
            'recaptcha_site_key' => '6LfRn3csAAAAAKPzWb8wMPDrP8k9qRNbh6ZA6E_I', // Výchozí klíč ze zadání
            'recaptcha_secret_key' => '6LfRn3csAAAAAH7X7gs09H8TJ8VCTX7lCDJLvldN', // Výchozí klíč ze zadání
            'recaptcha_threshold' => 0.5,
        ];

        $this->data = array_merge($defaults, $dbData);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make(__('admin/recaptcha-settings.sections.general'))
                    ->description(__('admin/recaptcha-settings.sections.general_desc'))
                    ->icon(new HtmlString('<i class="fa-light fa-shield-check text-primary mr-2"></i>'))
                    ->schema([
                        Toggle::make('recaptcha_enabled')
                            ->label(__('admin/recaptcha-settings.fields.enabled'))
                            ->helperText('Pokud je vypnuto, formuláře nebudou vyžadovat ověření.')
                            ->default(false),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('recaptcha_site_key')
                                    ->label(__('admin/recaptcha-settings.fields.site_key'))
                                    ->helperText('Veřejný klíč (Site Key) pro frontend.')
                                    ->password()
                                    ->revealable()
                                    ->required(fn ($get) => $get('recaptcha_enabled')),

                                TextInput::make('recaptcha_secret_key')
                                    ->label(__('admin/recaptcha-settings.fields.secret_key'))
                                    ->helperText('Tajný klíč (Secret Key) pro komunikaci se serverem Google.')
                                    ->password()
                                    ->revealable()
                                    ->required(fn ($get) => $get('recaptcha_enabled')),
                            ]),

                        TextInput::make('recaptcha_threshold')
                            ->label(__('admin/recaptcha-settings.fields.threshold'))
                            ->helperText('Práh citlivosti (0.0 až 1.0). Čím vyšší číslo, tím přísnější kontrola (0.5 je standard).')
                            ->numeric()
                            ->step(0.1)
                            ->minValue(0.0)
                            ->maxValue(1.0)
                            ->default(0.5)
                            ->maxWidth('xs'),
                    ]),

                Section::make('Nápověda k reCAPTCHA v3')
                    ->description('Jak získat klíče a nastavit ochranu')
                    ->icon(new HtmlString('<i class="fa-light fa-circle-info text-info mr-2"></i>'))
                    ->collapsed()
                    ->schema([
                        new HtmlString('
                            <div class="prose dark:prose-invert max-w-none text-sm">
                                <p>Tento web používá <strong>Google reCAPTCHA v3</strong>. Tato verze nevyžaduje od uživatelů klikání na obrázky, ale vrací skóre na základě jejich chování.</p>
                                <ol>
                                    <li>Přejděte na <a href="https://www.google.com/recaptcha/admin" target="_blank" class="text-primary underline">Google reCAPTCHA Admin Console</a>.</li>
                                    <li>Vytvořte nový web typu <strong>v3</strong>.</li>
                                    <li>Přidejte doménu (např. <code>kbelstisokoli.cz</code>).</li>
                                    <li>Zkopírujte klíče a vložte je výše.</li>
                                </ol>
                                <div class="bg-primary/5 p-4 rounded-xl border border-primary/10 mt-4">
                                    <p class="font-bold text-primary mb-1 italic">Klíče pro tento projekt:</p>
                                    <code class="block bg-white p-2 rounded border mb-2">Site key: 6LfRn3csAAAAAKPzWb8wMPDrP8k9qRNbh6ZA6E_I</code>
                                    <code class="block bg-white p-2 rounded border">Secret key: 6LfRn3csAAAAAH7X7gs09H8TJ8VCTX7lCDJLvldN</code>
                                </div>
                            </div>
                        '),
                    ]),
            ]);
    }

    public function save(): void
    {
        try {
            foreach ($this->data as $key => $value) {
                $setting = Setting::firstOrNew(['key' => $key]);

                // Protože je Setting model translatable, uložíme hodnotu pro všechna podporovaná locale,
                // aby klíče byly dostupné bez ohledu na aktuální jazyk.
                $locales = ['cs', 'en'];
                foreach ($locales as $locale) {
                    $setting->setTranslation('value', $locale, $value);
                }

                $setting->save();
            }

            Notification::make()
                ->title(__('admin/recaptcha-settings.notifications.saved'))
                ->success()
                ->seconds(3)
                ->send();

            $this->dispatch('recaptcha-saved');

        } catch (\Exception $e) {
            Notification::make()
                ->title(__('admin/recaptcha-settings.notifications.error'))
                ->body($e->getMessage())
                ->danger()
                ->seconds(4)
                ->send();
        }
    }
}
