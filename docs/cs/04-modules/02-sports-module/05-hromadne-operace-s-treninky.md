# Hromadné operace s tréninky

Tento modul umožňuje administrátorům efektivně plánovat a spravovat tréninky v dlouhodobém horizontu pomocí funkcí hromadného vytváření a hromadných úprav v seznamu.

## 1. Hromadné vytváření (Opakování)
Při vytváření nového tréninku je k dispozici sekce **Hromadné vytvoření (Opakování)**. Pokud je zvolena frekvence opakování, systém po uložení prvního (vzorového) tréninku automaticky vygeneruje celou sérii dalších tréninků.

### Podporované parametry
- **Frekvence:** Denně, Týdně (stejný den v týdnu), Měsíčně.
- **Ukončení série:**
    - **Počet opakování:** Uživatel zadá, kolik dalších tréninků se má vytvořit (např. 10).
    - **Období:** Uživatel vybere časové období (např. 1 měsíc, 3 měsíce nebo do konce aktuální sezóny).

### Technické detaily
- **Délka tréninku:** Pokud je zadán čas konce u prvního tréninku, je tato délka zachována u všech generovaných tréninků.
- **Relace:** Všechny vygenerované tréninky jsou automaticky přiřazeny stejným týmům jako vzorový trénink.
- **Sezóna:** Konec sezóny je definován k 31. červenci.

---

## 2. Hromadné akce v seznamu
V přehledu tréninků lze vybrat více záznamů najednou a provést s nimi následující akce (dostupné pod tlačítkem "Hromadné akce"):

- **Smazat:** Hromadné odstranění vybraných tréninků (včetně vazeb na týmy).
- **Změnit místo konání:** Umožňuje rychle přemístit vybrané tréninky do jiné haly či tělocvičny.
- **Změnit týmy:** Hromadně změní přiřazení týmů u všech vybraných tréninků (původní přiřazení je nahrazeno nově zvolenými týmy).

## Omezení
- Hromadné vytváření je dostupné pouze při **vytváření** nového tréninku.
- Maximální počet hromadně vytvořených tréninků je z bezpečnostních důvodů nastaven na 50 (přes číselník) resp. 100 (při výpočtu období).
