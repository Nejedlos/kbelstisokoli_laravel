# Hromadné vytváření tréninků

Tento modul umožňuje administrátorům efektivně plánovat tréninky v dlouhodobém horizontu pomocí funkce opakování.

## Funkcionalita
Při vytváření nového tréninku je k dispozici sekce **Hromadné vytvoření (Opakování)**. Pokud je zvolena frekvence opakování, systém po uložení prvního (vzrorového) tréninku automaticky vygeneruje celou sérii dalších tréninků.

### Podporované parametry
- **Frekvence:** Denně, Týdně (stejný den v týdnu), Měsíčně.
- **Ukončení série:**
    - **Počet opakování:** Uživatel zadá, kolik dalších tréninků se má vytvořit (např. 10).
    - **Období:** Uživatel vybere časové období (např. 1 měsíc, 3 měsíce nebo do konce aktuální sezóny).

## Technické detaily
- **Délka tréninku:** Pokud je zadán čas konce u prvního tréninku, je tato délka zachována u všech generovaných tréninků. Pokud čas konce zadán není, zůstane u všech tréninků prázdný.
- **Relace:** Všechny vygenerované tréninky jsou automaticky přiřazeny stejným týmům jako vzorový trénink.
- **Sezóna:** Při volbě "Do konce sezóny" systém počítá s koncem sezóny k 31. červenci. Pokud trénink začíná v srpnu, bere se jako konec červenec příštího roku.
- **Změna zadání:** Konec akce (`ends_at`) byl v databázi změněn na `nullable`, aby bylo možné zadávat tréninky bez fixního času konce.

## Omezení
- Funkce je dostupná pouze při **vytváření** nového tréninku. Při editaci již existujícího tréninku se sekce pro opakování nezobrazuje.
- Maximální počet hromadně vytvořených tréninků je z bezpečnostních důvodů nastaven na 50 (přes číselník) resp. 100 (při výpočtu období).
