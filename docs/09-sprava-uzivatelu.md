# Správa uživatelů (User Management)

Tento modul zajišťuje správu uživatelů, jejich registrace, přihlašování a přiřazování rolí a oprávnění.

## Technologie
- **Autentizace:** Laravel Fortify (registrace, login, reset hesla).
- **Autorizace:** Spatie Laravel Permission (role a oprávnění).
- **UI Administrace:** Filament PHP.

## První Administrátor
První superadmin uživatel byl vytvořen s údaji:
- **Jméno:** Michal Nejedlý
- **Email:** nejedlymi@gmail.com
- **Role:** `admin` (superadmin)
- **Stav:** `is_active = true`
- **Přihlašování:** Pomocí emailu a hesla.

## Modely
- `User` - Hlavní model pro uživatele.
- `Role` a `Permission` - Modely ze Spatie balíčku.
