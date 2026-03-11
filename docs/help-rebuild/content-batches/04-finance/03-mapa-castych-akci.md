# Mapa častých akcí - Batch 04: Finance

Tento dokument definuje konkrétní kroky pro nejčastější úkony ve finančním modulu na základě reálného UI.

## 1. Vytvoření předpisu platby pro člena

- **Kde:** Finance -> Předpisy plateb -> Nový předpis
- **Kdo:** admin
- **Kroky:**
    1. Klikněte na tlačítko "Nový předpis".
    2. V poli "Člen (Uživatel)" vyhledejte hráče/člena.
    3. Vyberte "Typ platby" (např. Členský příspěvek).
    4. Zadejte "Název / Účel" (např. "Příspěvky Jaro 2024").
    5. V sekci "Finanční detaily" zadejte celkovou částku a datum splatnosti.
    6. Ujistěte se, že "Stav" je nastaven na "Otevřeno (K úhradě)".
    7. Uložte záznam.
- **Rizika:** Pokud zapomenete nastavit "Viditelné pro člena", člen se o platbě nedozví.
- **Časté chyby:** Nastavení špatného typu platby, což pak zkresluje filtry v seznamu.

## 2. Evidence přijaté bankovní platby

- **Kde:** Finance -> Platby -> Nová platba
- **Kdo:** admin
- **Kroky:**
    1. Klikněte na "Nová platba".
    2. Vyberte "Plátce", pokud je zřejmý (pokud ne, nechte prázdné a dohledejte později).
    3. Zadejte částku a datum přijetí (podle bankovního výpisu).
    4. V sekci "Identifikace platby" vyplňte Variabilní symbol a ID transakce (volitelně).
    5. Do "Poznámka z výpisu" zkopírujte text z banky (např. jméno plátce).
    6. Uložte záznam.
- **Předpoklady:** Musíte mít k dispozici bankovní výpis.

## 3. Párování (Alokace) platby k dluhu

- **Kde:** Detail konkrétní platby -> Tab "Přiřazeno k předpisům"
- **Kdo:** admin
- **Kroky:**
    1. Otevřete záznam platby (klikněte na "Upravit" v seznamu Plateb).
    2. Přejděte na záložku "Přiřazeno k předpisům" (dole nebo v bočním menu relací).
    3. Klikněte na "Přiřadit k předpisu".
    4. Vyberte předpis (systém nabízí pouze ty neuhrazené od daného uživatele).
    5. Systém předvyplní zbývající částku nebo dostupnou částku platby.
    6. Potvrďte "Vytvořit".
- **Dopady:** Stav předpisu se automaticky změní na "Zaplaceno" (pokud byla uhrazena celá částka) nebo "Částečně zaplaceno".

## 4. Stornování chybně zadané platby

- **Kde:** Finance -> Platby -> Detail platby
- **Kdo:** admin
- **Kroky:**
    1. Otevřete platbu.
    2. V poli "Stav záznamu" vyberte "Stornováno / Vráceno".
    3. Pokud na platbu byly navázány alokace, je nutné je smazat v tabulce alokací, aby se předpisy vrátily do stavu "K úhradě".
    4. Uložte záznam.
- **Rizika:** Samotná změna stavu platby na "Stornováno" automaticky neruší alokace, ty se musí odebrat ručně.

## 5. Kontrola neplatičů (Po splatnosti)

- **Kde:** Finance -> Předpisy plateb
- **Kdo:** admin, coach
- **Kroky:**
    1. V seznamu Předpisů plateb klikněte na filtr "Stav".
    2. Vyberte hodnotu "Po splatnosti" (overdue).
    3. Seznam se zúží na dluhy, u kterých již uplynulo datum splatnosti a nejsou plně uhrazeny.
- **Tipy:** Červený indikátor u data splatnosti vizuálně upozorňuje na kritické položky.
