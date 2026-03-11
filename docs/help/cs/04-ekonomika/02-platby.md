# Platby

V této sekci evidujete všechny příchozí i odchozí finanční transakce klubu.

Nápověda > Ekonomika > Platby

### Pro koho je sekce určena
- Ekonom / Účetní
- Administrátor

### Vysvětlení sekce
Zde vidíte seznam všech zaznamenaných plateb. Platby mohou vznikat automaticky (např. importem z banky) nebo je můžete zadávat ručně. Každá platba by měla být spárována s konkrétním uživatelem a předpisem platby.

### Nejčastější akce

#### Ruční vytvoření platby
1. Klikněte na **Vytvořit platbu**.
2. Vyberte **Uživatele**, který platbu provedl.
3. Zadejte **Částku** a **Datum přijetí**.
4. Vyberte **Metodu platby** (např. převod, hotovost).
5. Pokud znáte **Variabilní symbol**, vyplňte jej.
6. Potvrďte tlačítkem **Vytvořit**.

#### Spárování platby s předpisem
1. Otevřete detail platby.
2. V poli **Předpis platby** vyhledejte příslušný předpis (např. "Členský příspěvek 2026/27").
3. Klikněte na **Uložit**. Tím se daný předpis označí jako (částečně) zaplacený.

#### Kontrola neuhrazených plateb
1. V tabulce plateb použijte filtr **Stav**.
2. Vyberte možnost **Čekající** nebo **Neuhrazeno**.
3. Seznam se zúží na uživatele, kteří dluží.

### Popis obrazovky
- **Tabulka plateb:** Seznam transakcí se základními údaji.
- **Badge stavu:** Informace o tom, zda je platba zaplacena, stornována nebo čeká.
- **Filtry:** Umožňují vyhledávat podle jména, částky nebo data.

### Vysvětlení polí
- **Uživatel:** Osoba, ke které se platba vztahuje.
- **Částka:** Hodnota transakce v CZK.
- **Variabilní symbol:** Klíčový údaj pro automatické párování.
- **Poznámka:** Interní informace o platbě.

### Časté chyby a upozornění
- **Špatný variabilní symbol:** Pokud se platba nespáruje automaticky, zkontrolujte, zda uživatel uvedl správný VS.
- **Duplicitní platby:** Pozor na ruční zadávání plateb, které už byly importovány z banky.
- **Storno platby:** Místo smazání platby ji raději stornujte změnou stavu, aby zůstala zachována historie.

### Související sekce
- [Předpisy plateb](01-predpisy-plateb.md)
- [Uživatelé](../03-lide-a-clenove/01-uzivatele.md)
