# Evidence a import plateb

Finanční modul systému KS slouží ke sledování veškerých příjmů klubu. Správná evidence plateb je nezbytná pro přehled o dluzích členů a pro generování podkladů pro účetnictví.

### Importy z banky (Kritické)
Nejefektivnější způsob zadávání plateb je hromadný import bankovního výpisu.
- **Akce:** V přehledu plateb použijte tlačítko **Importovat platby**.
- **Formát:** Systém podporuje standardní CSV/GPC formáty bankovních výpisů (Fio, Partners, atd.).
- **Průběh:** Systém se pokusí automaticky spárovat platbu s členem podle **Variabilního symbolu (VS)** nebo podle názvu účtu v poznámce.
- **Důležité:** Vždy po importu zkontrolujte "Nespárované platby". Ty musíte přiřadit ručně.

### Typy plateb
Platba může být do systému zadána několika způsoby:
1. **Bankovní převod:** Automaticky importován nebo zadán ručně s uvedením data připsání na účet.
2. **Hotovost:** Zadává pokladník nebo trenér při výběru peněz na tréninku/akci.
3. **Přeplatek / Interní:** Využívá se při přesunu financí mezi sezónami nebo při opravách chyb.

### Ruční zadání platby
Pokud zadáváte platbu ručně, dbejte na následující pole:
- **Člen (User):** Komu platba patří.
- **Částka:** Skutečně přijatá suma.
- **Datum:** Den, kdy byly peníze přijaty.
- **Variabilní symbol:** Pokud byl uveden, zjednoduší to budoucí párování.

### Statusy plateb
- **Nepotvrzeno:** Platba čeká na schválení administrátorem (např. u ručních zadání trenéry).
- **Potvrzeno:** Platba je platná a započítává se do bilance člena.
- **Stornováno:** Platba, která byla zadána chybně nebo se vrátila jako neúspěšná.

### Časté chyby
- **Špatný Variabilní symbol:** Pokud hráč zadá špatný VS, systém platbu nespáruje. Musíte ji v seznamu nespárovaných plateb ručně dohledat a přiřadit ke správnému uživateli.
- **Duplicitní import:** Systém se snaží duplicitám předcházet kontrolou ID transakce, ale při ručním zadání buďte obezřetní.
