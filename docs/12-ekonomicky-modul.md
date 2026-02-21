# Ekonomický modul (Economy Management)

Tento modul slouží ke správě financí, členských příspěvků a plateb.

## Funkcionalita
- Evidence plateb členů.
- Automatické generování platebních předpisů (příspěvků).
- Sledování stavu plateb (uhrazeno, dlužné).
- Exporty dat (CSV, PDF).

## Modely
- `Payment` - Evidence plateb.
- `Contribution` - Definice členských příspěvků.
- `Transaction` - Jednotlivé pohyby na účtu/pokladně.

## Administrace
Všechny ekonomické funkce budou spravovány v administraci (Filament).
- Dashboard s finančními přehledy.
- Resource pro správu plateb a příspěvků.
