<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as FilamentLoginResponseContract;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    // Použij vlastní layout místo výchozího jednoduchého layoutu Filamentu
    protected static string $layout = 'filament.admin.layouts.auth';

    // DŮLEŽITÉ: `$view` musí být NEstatická vlastnost, aby odpovídala `Filament\Pages\SimplePage`
    protected string $view = 'filament.admin.auth.login';

    public function getHeading(): string|Htmlable
    {
        return __('Vstup do kabiny');
    }

    public function getSubheading(): string|Htmlable
    {
        return __('Z palubovky rovnou k taktické tabuli.');
    }

    public function getIcon(): string
    {
        return 'fa-basketball-hoop';
    }

    protected function getPasswordFormComponent(): Component
    {
        // Přepisujeme původní metodu, abychom odstranili helper link "Zapomněli jste heslo?",
        // který Filament automaticky přidává, protože ho máme v custom layoutu.
        return parent::getPasswordFormComponent()
            ->helperText(null)
            ->hint(null);
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return null; // Don't send notification, handle via exception
    }

    protected function getRateLimitedException(TooManyRequestsException $exception): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('auth.throttle', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => ceil($exception->secondsUntilAvailable / 60),
            ]),
        ]);
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('auth.failed'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return parent::form($schema)
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    public function authenticate(): ?FilamentLoginResponseContract
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedException($exception);
        }

        $data = $this->form->getState();
        $credentials = $this->getCredentialsFromFormData($data);

        // Najdeme uživatele pro předběžnou kontrolu aktivity
        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if (! $user->is_active) {
                Log::warning('Filament Auth: Login attempt for inactive user', ['email' => $user->email]);

                throw ValidationException::withMessages([
                    'data.email' => __('Váš účet není aktivní. Kontaktujte prosím tým pro aktivaci.'),
                ]);
            }
        }

        // Pokus o přihlášení (standardní cesta)
        if (! Filament::auth()->attempt($credentials, $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        // Pokud je to admin, zkontrolujeme přístup k panelu (standardní Filament chování, ale bez okamžitého logoutu)
        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            // Uživatel nemá přístup k tomuto panelu (např. hráč v adminu).
            // NEodhlašujeme ho (pokud je heslo správné, chceme, aby zůstal přihlášen pro členskou sekci),
            // ale necháme LoginResponse rozhodnout, kam ho poslat.
            Log::info('Login.authenticate: User lacks panel access but password is OK', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }

        session()->regenerate();
        session()->put('login.remember', (bool) ($data['remember'] ?? false));

        // Resolve the redirect while this Livewire component is active.
        app(FilamentLoginResponseContract::class)->toResponse(request());

        return null;
    }
}
