<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\LoginResponse;
use App\Http\Responses\LogoutResponse;
use App\Http\Responses\PasswordResetResponse;
use App\Http\Responses\TwoFactorLoginResponse;
use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as FilamentLoginResponseContract;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as FilamentLogoutResponseContract;
use Filament\Auth\Http\Responses\Contracts\PasswordResetResponse as FilamentPasswordResetResponseContract;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
        $this->app->singleton(TwoFactorLoginResponseContract::class, TwoFactorLoginResponse::class);
        $this->app->singleton(PasswordResetResponseContract::class, PasswordResetResponse::class);

        // Také registrujeme pro Filament kontrakty
        $this->app->singleton(FilamentLoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(FilamentLogoutResponseContract::class, LogoutResponse::class);
        $this->app->singleton(FilamentPasswordResetResponseContract::class, PasswordResetResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        // Vlastní logika autentizace pro kontrolu is_active
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                if (! $user->is_active) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        Fortify::username() => ['Váš účet momentálně není aktivní. Kontaktujte prosím správce svého týmu.'],
                    ]);
                }

                return $user;
            }

            return null;
        });

        // Mapování Fortify view na naše základní šablony
        Fortify::loginView(function () {
            \Illuminate\Support\Facades\Log::info('Fortify.loginView', [
                'session_id' => \Illuminate\Support\Facades\Session::getId(),
                'intended' => session('url.intended'),
            ]);

            return view('auth.login');
        });

        Fortify::twoFactorChallengeView(function () {
            \Illuminate\Support\Facades\Log::info('Fortify.twoFactorChallengeView.enter', [
                'user_id' => auth()->id(),
                'email' => auth()->user()?->email,
                'session_id' => \Illuminate\Support\Facades\Session::getId(),
                'intended' => session('url.intended'),
                'has_secret' => (bool) auth()->user()?->two_factor_secret,
                'confirmed' => (bool) auth()->user()?->two_factor_confirmed_at,
            ]);

            return view('auth.two-factor-challenge');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view('auth.forgot-password');
        });

        Fortify::resetPasswordView(function (Request $request) {
            return view('auth.reset-password', ['request' => $request]);
        });

        Fortify::confirmPasswordView(function () {
            return view('auth.confirm-password');
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
