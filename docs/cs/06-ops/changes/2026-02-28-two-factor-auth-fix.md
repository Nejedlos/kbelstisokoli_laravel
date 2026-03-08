# Změna: Oprava Unauthenticated po 2FA autentizaci

## Datum: 2026-02-28
## Autor: Junie

### Popis problému
Při přístupu do administrace (Filament) po vypršení 2FA timeoutu byl uživatel přesměrován na 2FA výzvu. Po úspěšném zadání kódu a návratu do administrace však došlo k výjimce `Illuminate\Auth\AuthenticationException: Unauthenticated.` a uživatel byl odhlášen.

### Technická příčina
Filament v rámci svého middleware stacku používá `Filament\Http\Middleware\AuthenticateSession`, který pro ověření platnosti session vyžaduje přítomnost hashovaného hesla uživatele v session (pod klíčem `password_hash_{guard}`). 

Během standardního 2FA procesu Fortify (zejména pokud je vynucen dodatečně uprostřed session přes `CheckTwoFactorTimeout`) dochází k regeneraci session ID. Pokud v této nově zregenerované session není tento klíč korektně nastaven, Filament uživatele v dalším požadavku (po redirectu) vyhodnotí jako neautorizovaného.

### Řešení
Upravili jsme `App\Http\Responses\TwoFactorLoginResponse.php`, aby před finálním přesměrováním uživatele zpět na zamýšlenou URL zajistila explicitní uložení `password_hash_{guard}` do session.

```php
        $user = auth()->user();
        $guard = auth()->getDefaultDriver();
        $request->session()->put([
            "password_hash_{$guard}" => $user->getAuthPassword(),
            'auth.2fa_confirmed_at' => now()->timestamp,
        ]);
```

### Ověření
- V lokálním prostředí proběhne 2FA přihlášení korektně a uživatel zůstává přihlášen.
- Logování v `TwoFactorLoginResponse.session_prepared` potvrzuje, že klíč je v session přítomen před redirectem.
