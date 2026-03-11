# UI Audit: Batch 01 – Úvod a Onboarding

Tento dokument obsahuje detailní technický a UX rozbor skutečného stavu systému pro oblast úvodního seznámení uživatele s aplikací, přihlášení a správy profilu.

## 1. Analyzované stránky a technické komponenty

### Auth Flow (Přihlášení a obnova)
- **Login Page**: `App\Filament\Pages\Auth\Login`
    - View: `resources/views/filament/admin/auth/login.blade.php` (Custom layout `filament.admin.layouts.auth`)
    - Route: `/admin/login` (Admin/Filament) a pravděpodobně i přesměrování z členské sekce.
- **Request Password Reset**: `App\Filament\Pages\Auth\RequestPasswordReset`
    - Route: `/admin/password-reset/request`
- **Reset Password**: `App\Filament\Pages\Auth\ResetPassword`
    - Route: `/admin/password-reset/{token}`
- **Two-Factor Setup**: `App\Http\Controllers\Auth\TwoFactorSetupController`
    - View: `auth.two-factor-setup`
    - Povinné pro role s přístupem do administrace (`admin`, `coach`).

### Členská sekce (Profil a Dashboard)
- **Dashboard**: `App\Http\Controllers\Member\DashboardController`
    - View: `resources/views/member/dashboard.blade.php`
    - Prvky: "Nudges" (doporučení) pro nahrání avataru, chybějící profil apod.
- **Profile Edit**: `App\Http\Controllers\Member\ProfileController`
    - View: `resources/views/member/profile/edit.blade.php`
- **Avatar Management**: Livewire komponenta `AvatarModal`
    - Umožňuje nahrání vlastní fotky nebo výběr z galerie basketbalových ikon.

## 2. Skutečné UI prvky a terminologie

### Přihlášení (Basketbalová metafora)
- **Nadpis**: "Vstup do kabiny"
- **Podtitul**: "Z palubovky rovnou k taktické tabuli."
- **Tlačítko**: "Vstoupit na palubovku"
- **Chybové hlášky**: "Špatná nahrávka!", "Přešlap!" (viz `lang/cs.json`)

### Můj profil (Pole a formuláře)
- **Osobní údaje**:
    - `name` (Jméno a příjmení)
    - `phone` (Telefonní číslo)
    - `public_bio` (Bio / Krátké představení)
    - `jersey_number` (Číslo dresu)
- **Nastavení zobrazení**:
    - `member_default_team_id` (Výchozí tým pro filtraci)
    - `member_view_all_by_default` (Přepínač "Zobrazit vše ve výchozím stavu")
- **Bezpečnost**:
    - `current_password`, `new_password`, `new_password_confirmation`.
    - **2FA (Neprůstřelná obrana)**: QR kód pro authenticator, 8-místné záchranné kódy.

### Vizuální komponenty
- **Player Card**: Komponenta na stránce profilu s přehledem týmů a stavu hráče.
- **Badge**: Role se zobrazují jako štítky (např. "Trenér", "Hráč").

## 3. Role a oprávnění (Visibility)

Systém rozlišuje přístup na základě rolí definovaných v `RoleSeeder`:
- **Administrativní role**: `super_admin`, `admin`, `coach`, `editor`.
    - Vidí odkaz na "Administraci" v menu.
    - Mají povinné 2FA ("Jako člen realizačního týmu máš přístup k taktice celého klubu").
- **Členské role**: `player`, `parent`.
    - Přístup pouze do členské sekce.
    - 2FA je volitelné.

## 4. Návrh témat článků (Backlog pro Batch 01)

1. **První kroky a přihlášení**
    - Jak získat přístup (systém je uzavřený, registrace adminem).
    - Obnova zapomenutého hesla.
2. **Kompletní nastavení profilu**
    - Proč vyplňovat Bio a Číslo dresu.
    - Jak funguje filtr "Aktivní tým" v navigaci.
3. **Zabezpečení účtu a 2FA**
    - Nastavení dvoufázového ověření (povinnost vs. doporučení).
    - Jak pracovat se záchrannými kódy.
4. **PWA – Aplikace do mobilu**
    - Jak si přidat web na plochu přes `site.webmanifest`.
5. **Role v systému (Kdo je kdo)**
    - Rozdíl mezi hráčem, rodičem a trenérem v UI.

## 5. Nejasnosti k prověření (Open Issues)

- [ ] **Správa dětí (Rodič)**: V DB jsou vztahy `children()`, ale v UI členské sekce zatím není vidět explicitní přepínač profilu dítěte. Nutno zjistit, zda rodič spravuje dítě v rámci svého profilu nebo přes impersonifikaci.
- [ ] **Registrace**: `Features::registration()` je vypnuté. Je potřeba jasně popsat proces, jak se nový uživatel do systému dostane (přes Náborový formulář -> schválení adminem).
- [ ] **Email Verification**: V konfiguraci Fortify je vypnuto. Ověřit, zda se posílá uvítací email s odkazem na nastavení hesla.

## 6. Podložená skutečnost vs. Inference

- **Skutečnost**: Názvy polí, basketbalová terminologie v `cs.json`, 2FA logika, vypnutá registrace.
- **Inference**: Předpokládám, že Náborový formulář na webu je hlavní vstupní branou pro nové hráče (vzhledem k vypnuté registraci ve Fortify).
- **Návrh**: PWA článek je založen na existenci manifestu, ale v UI zatím chybí "Install" banner.
