# 2026-05-05 - Zabezpečení 2FA a Nudges

## Změny
- **Bezpečnost:** Implementována povinnost mít aktivní 2FA pro všechny uživatele s přístupem do administrace.
    - Přidán middleware `Restrict2FADeactivation`, který blokuje deaktivaci 2FA pro adminy na úrovni backendu.
    - Upraveno UI profilu pro skrytí možnosti deaktivace pro adminy.
- **UX (Dashboard):** Přidán nový "nudge" (doporučení) na dashboard členské sekce, který neagresivně vybízí uživatele bez aktivního 2FA k zabezpečení účtu.
- **Navigace:** Nudge nyní odkazuje přímo na sekci 2FA v profilu pomocí fragmentu `#two-factor-setup`.
- **Lokalizace:** Přidány překlady pro nové prvky a varování v češtině a angličtině.

## Soubory
- `app/Http/Controllers/Member/DashboardController.php` (logika nudges)
- `app/Http/Middleware/Restrict2FADeactivation.php` (bezpečnostní pojistka)
- `bootstrap/app.php` (registrace middleware)
- `resources/views/member/profile/edit.blade.php` (úprava UI profilu)
- `lang/cs/member.php`, `lang/en/member.php` (lokalizace)
- `lang/cs.json`, `lang/en.json` (lokalizace nudges)
