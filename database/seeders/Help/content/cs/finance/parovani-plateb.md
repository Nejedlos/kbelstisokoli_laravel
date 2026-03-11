# Alokace a párování plateb

Alokace je proces, kdy systém "přiřazuje" přijaté peníze z **Platby** ke konkrétnímu **Předpisu**. Teprve po úspěšné alokaci se předpis změní na "Zaplaceno" a členovi zmizí dluh.

### Automatické párování
Systém se snaží šetřit čas a většinu plateb páruje automaticky při importu:
1. **Podle Variabilního symbolu:** Pokud se VS u platby přesně shoduje s VS u předpisu, systém vytvoří alokaci okamžitě.
2. **Podle Člena:** Pokud člen pošle více peněz bez VS, ale má v dané sezóně jen jeden nezaplacený předpis, systém může platbu navrhnout k přiřazení.

### Ruční alokace (Technický postup)
Pokud se platba nepárovala automaticky (např. chybějící VS), musíte ji přiřadit ručně:
1. Přejděte do sekce **Platby** a najděte danou platbu.
2. V detailu platby (nebo v tabulce akcí) najděte sekci **Alokace**.
3. Klikněte na **Vytvořit alokaci**.
4. Vyberte předpis, který má být touto platbou pokryt.
5. Zadejte částku alokace (platba může pokrývat i více předpisů, např. sourozence).

### Jak systém řeší částky
- **Částečná alokace:** Pokud člen pošle jen část peněz, alokujte tuto část. Předpis zůstane ve stavu "Částečně zaplaceno".
- **Přeplatek:** Pokud člen pošle více, systém vytvoří alokaci v plné výši předpisu. Zbytek platby zůstane "nealokován" a můžete jej buď nechat jako přeplatek pro budoucí předpis, nebo vrátit.

### Zrušení alokace
Pokud jste platbu přiřadili špatnému členovi nebo špatnému předpisu, můžete alokaci smazat:
- **Důsledek:** Smazáním alokace se předpis opět stane "Nezaplaceným" a platba se stane "Nealokovanou". Peníze se nesmažou, jen se uvolní k jinému přiřazení.

### Proč je to důležité?
Bez alokace systém neví, že je konkrétní předpis zaplacen, i když peníze od daného člověka už v bance vidíte. Členská sekce pak bude hráči stále hlásit dluh.
