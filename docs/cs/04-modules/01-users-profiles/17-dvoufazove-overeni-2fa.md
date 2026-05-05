# Dvoufázové ověření (2FA)

Tento modul zajišťuje zvýšenou bezpečnost uživatelských účtů prostřednictvím dvoufázového ověření (standard TOTP, např. Google Authenticator).

## 1. Povinnost pro administrátory
Z důvodu bezpečnosti klubu je **dvoufázové ověření povinné pro všechny uživatele s přístupem do administrace** (role administrátor, trenér a další s oprávněním `access_admin`).

### Implementované pojistky:
- **UI Pojistka:** V nastavení profilu (`member.profile.edit`) je pro tyto uživatele skryto tlačítko pro deaktivaci 2FA. Místo něj se zobrazuje varování vysvětlující, že jejich role vyžaduje aktivní 2FA.
- **Backend Pojistka:** Middleware `Restrict2FADeactivation` kontroluje requesty na routu `two-factor.disable`. Pokud se o deaktivaci pokusí uživatel s přístupem do administrace, request je zablokován chybou 403.

## 2. Propagace (Nudges)
Abychom motivovali i běžné hráče k zabezpečení účtu, systém obsahuje neagresivní výzvy na dashboardu:
- **Dashboard Nudge:** Pokud uživatel nemá 2FA aktivní, systém mu náhodně (v rotaci s jinými úkoly, např. nahraním fotky) navrhuje jeho aktivaci.
- **Navedení (Pipeline):** Tlačítko v nudge vede přímo na sekci 2FA v profilu (`#two-factor-setup`), kde je připraven srozumitelný průvodce aktivací ve třech krocích.

## 3. Technické detaily
- **Framework:** Laravel Fortify.
- **Middleware:** `App\Http\Middleware\Restrict2FADeactivation` (registrován v `bootstrap/app.php`).
- **Lokalizace:** Všechny texty a varování jsou dostupné v češtině i angličtině (`lang/cs/member.php`, `lang/cs.json`).
- **Identifikace adminů:** Používá se metoda `$user->canAccessAdmin()`, která zahrnuje kontrolu rolí i přímých oprávnění.
