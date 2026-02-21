# Správa uživatelů (User Management)

Tento modul zajišťuje správu uživatelů, jejich registrace, přihlašování a přiřazování rolí a oprávnění.

## Technologie
- **Autentizace:** Laravel Fortify (registrace, login, reset hesla).
- **Autorizace:** Spatie Laravel Permission (role a oprávnění).
- **UI Administrace:** Filament PHP.

## Role
V systému se počítá s následujícími základními rolemi:
1. **Super Admin:** Plný přístup ke všem funkcím.
2. **Admin:** Přístup k administraci (bez možnosti měnit kritické nastavení).
3. **Vedoucí:** Správa oddílů a členů.
4. **Člen:** Základní přístup k vlastním datům.

## Modely
- `User` - Hlavní model pro uživatele.
- `Role` a `Permission` - Modely ze Spatie balíčku.
