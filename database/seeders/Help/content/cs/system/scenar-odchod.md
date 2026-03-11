# Scénář: Ukončení členství (Exit process)

Když se hráč nebo trenér rozhodne v klubu skončit, je důležité správně uzavřít jeho působení v systému, aby mu nechodily upomínky na platby a neměl přístup k interním datům.

## Krok 1: Kontrola financí
1. Otevřete profil uživatele a podívejte se na **Finanční vyúčtování**.
2. Pokud má hráč dluh, dohodněte se na jeho vyrovnání.
3. Pokud má přeplatek, systém jej v budoucnu nevrací automaticky, je třeba to vyřešit účetně.

## Krok 2: Vypnutí plateb
1. V profilu uživatele na záložce **Klub** (v konfiguraci sezóny) nastavte **Billing end month** (měsíc ukončení plateb).
2. Tím zajistíte, že od příštího měsíce již systém nebude generovat nové předpisy (příspěvky).

## Krok 3: Deaktivace účtu
1. V seznamu uživatelů u daného člověka přepněte příznak **is_active** (Aktivní) na **False**.
2. Tím uživateli okamžitě zamezíte přístup do administrace i do členské sekce.
3. **Důležité**: Uživatele nemažte úplně, pokud chcete zachovat jeho historické statistiky a záznamy o platbách.

## Krok 4: Odstranění ze soupisek
1. Odeberte uživatele ze všech aktivních týmů, aby se neobjevoval v seznamech pro docházku a nominace.

## Krok 5: Vrácení vybavení
1. Pokud má uživatel zapůjčený dres nebo jiné vybavení, zkontrolujte jeho vrácení.

## Tipy
- **Archivace**: Deaktivovaní uživatelé zůstávají v databázi. Můžete je kdykoli znovu aktivovat, pokud se do klubu vrátí.
