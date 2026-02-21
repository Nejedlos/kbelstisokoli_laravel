<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Základní údaje')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Jméno a příjmení')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('E-mailová adresa')
                                    ->email()
                                    ->required()
                                    ->unique('users', 'email', ignoreRecord: true)
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('Telefonní číslo')
                                    ->tel()
                                    ->maxLength(255),
                                Select::make('roles')
                                    ->label('Role uživatele')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),
                            ]),
                    ]),

                Section::make('Zabezpečení')
                    ->description('Ponechte heslo prázdné, pokud jej nechcete měnit.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('password')
                                    ->label('Heslo')
                                    ->password()
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                    ->maxLength(255),
                                Toggle::make('is_active')
                                    ->label('Aktivní účet')
                                    ->helperText('Deaktivovaný uživatel se nebude moci přihlásit.')
                                    ->default(true),
                            ]),
                    ]),

                Section::make('Dvoufázové ověření (2FA)')
                    ->description('Správa zabezpečení účtu pomocí druhého faktoru.')
                    ->aside()
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                \Filament\Forms\Components\Placeholder::make('2fa_status_detailed')
                                    ->label('Aktuální stav')
                                    ->content(fn ($record) => match (true) {
                                        !$record?->two_factor_secret => '❌ Neaktivní',
                                        !$record->two_factor_confirmed_at => '⚠️ Čeká na potvrzení (QR vygenerován)',
                                        default => '✅ Aktivní a ověřeno',
                                    }),
                                \Filament\Forms\Components\Placeholder::make('2fa_confirmed_at')
                                    ->label('Datum aktivace')
                                    ->content(fn ($record) => $record?->two_factor_confirmed_at?->format('d.m.Y H:i') ?? '-')
                                    ->visible(fn ($record) => $record?->two_factor_confirmed_at !== null),
                            ]),
                    ])
                    ->headerActions([
                        \Filament\Actions\Action::make('disable_2fa')
                            ->label('Vypnout / Resetovat 2FA')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->modalHeading('Opravdu chcete vypnout 2FA pro tohoto uživatele?')
                            ->modalDescription('Uživatel bude muset při příštím přihlášení do adminu 2FA znovu nastavit (pokud je admin).')
                            ->action(function ($record) {
                                $record->update([
                                    'two_factor_secret' => null,
                                    'two_factor_recovery_codes' => null,
                                    'two_factor_confirmed_at' => null,
                                ]);
                                \Filament\Notifications\Notification::make()
                                    ->title('2FA bylo úspěšně vypnuto')
                                    ->success()
                                    ->send();
                            })
                            ->visible(fn ($record) => $record?->two_factor_secret !== null)
                            ->authorize(fn ($record) => auth()->user()?->can('manage_users') || ($record && $record->id === auth()->id())),

                        \Filament\Actions\Action::make('regenerate_recovery_codes')
                            ->label('Regenerovat kódy')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading('Regenerovat záchranné kódy?')
                            ->modalDescription('Původní záchranné kódy přestanou platit. Nové kódy si budete muset uložit.')
                            ->action(function ($record) {
                                $record->replaceRecoveryCodes();
                                \Filament\Notifications\Notification::make()
                                    ->title('Nové záchranné kódy byly vygenerovány')
                                    ->body('Kódy si můžete zobrazit pomocí tlačítka "Zobrazit kódy".')
                                    ->warning()
                                    ->send();
                            })
                            ->visible(fn ($record) => $record && $record->id === auth()->id() && $record->two_factor_confirmed_at !== null),

                        \Filament\Actions\Action::make('view_recovery_codes')
                            ->label('Zobrazit kódy')
                            ->color('gray')
                            ->modalHeading('Vaše záchranné kódy')
                            ->form([
                                TextInput::make('current_password')
                                    ->label('Pro zobrazení kódů potvrďte své heslo')
                                    ->password()
                                    ->required()
                                    ->rule(fn () => function (string $attribute, $value, $fail) {
                                        if (! \Illuminate\Support\Facades\Hash::check($value, auth()->user()->password)) {
                                            $fail('Zadané heslo není správné.');
                                        }
                                    })
                            ])
                            ->action(function ($record) {
                                // Akce po úspěšném odeslání formuláře (ověření hesla)
                                // Zobrazíme kódy v persistentní notifikaci, protože v Filamentu 3 je těžké znovu otevřít modal s daty
                                $codes = collect($record->recoveryCodes())->map(fn($c) => "<code>$c</code>")->implode('<br>');
                                \Filament\Notifications\Notification::make()
                                    ->title('Vaše záchranné kódy')
                                    ->body(new \Illuminate\Support\HtmlString("Tyto kódy si bezpečně uložte:<br><br><div class='font-mono text-lg font-bold tracking-widest'>$codes</div>"))
                                    ->success()
                                    ->persistent()
                                    ->send();
                            })
                            ->visible(fn ($record) => $record && $record->id === auth()->id() && $record->two_factor_confirmed_at !== null),
                    ]),

                Section::make('Administrativní poznámka')
                    ->collapsed()
                    ->schema([
                        Textarea::make('admin_note')
                            ->label('Interní poznámka k uživateli')
                            ->placeholder('Zadejte interní info o členovi...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
