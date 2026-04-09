# Operace: Hromadné označení financí za uhrazené

Datum: 2026-04-09
Autor: Junie

## Účel
Na žádost uživatele byla vytvořena migrace pro hromadné označení všech rozpracovaných předpisů (`finance_charges`) a zapsaných plateb (`finance_payments`) za uhrazené/potvrzené. Tato operace slouží k vyrovnání historických dluhů v systému.

## Provedené změny
- Vytvořena migrace `database/migrations/2026_04_09_175616_mark_all_finances_as_paid.php`.
- **FinanceCharge:** Všechny záznamy ve stavech `open`, `partially_paid` a `overdue` byly změněny na `paid`.
- **FinancePayment:** Všechny záznamy ve stavu `recorded` byly změněny na `confirmed`.

## Dopady na UX
- V administraci i na klientském dashboardu se dlužná částka pro všechny členy vynuluje.
- Historické záznamy budou v přehledech figurovat jako "Uhrazeno" (Charges) a "Potvrzeno" (Payments).

## Poznámky
- Migrace je jednosměrná (metoda `down()` je prázdná), protože neexistuje bezpečný způsob, jak se vrátit k původním rozpracovaným stavům bez znalosti historie změn.
