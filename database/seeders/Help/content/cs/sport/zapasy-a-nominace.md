Sekce **Zápasy** pokrývá kompletní životní cyklus utkání – od automatického rozpisů až po zápis podrobných statistik a výsledků.

### Automatizace: Synchronizace s cz.basketball (ČBF)
Tato funkce je pro klub Kbelští sokoli zásadní a šetří hodiny administrativní práce.
- **Jak to funguje**: U týmu s povolenou synchronizací systém pravidelně stahuje rozpis a výsledky z webu ČBF/PBasket. Nová utkání založí, existující aktualizuje podle externího ID.
- **Externí indikátor**: V tabulce zápasů poznáte synchronizovaný zápas podle modré <i class="fa-light fa-cloud-arrow-down text-info"></i> **Ikony cloudu** u datumu zápasu.
- **Hromadná AI synchronizace**: Pokud u historických zápasů chybí detaily (body, fauly hráčů), lze v hromadných akcích vybrat zápasy a spustit "AI Synchronizaci detailů". Ta se pokusí automaticky "vyčíst" a přiřadit statistiky k hráčům v databázi.

### Plánování a správa zápasu
U přátelských utkání nebo turnajů, které nejsou v ČBF, musíte zápas vytvořit ručně.
1. **Základní údaje**: Výběr domácího týmu, soupeře, data, času a místa. Do pole **Soupeř** napište celý název. Existující tým vyberte z výsledků; pokud přesně stejný název ještě není v adresáři, zvolte **Vytvořit tým „…“**. Soupeř se ihned založí pouze s názvem a rovnou se vybere, bez dalšího formuláře nebo přechodu na jinou stránku.
2. **Místo konání (Hala)**: U každého zápasu lze specifikovat konkrétní halu. Pokud je to hala Kbely, automaticky se nabízí s mapou.
3. **Status zápasu**: Plánováno (vytvořeno), Naplánováno (potvrzen termín), Odehráno (zapsán výsledek), Odloženo/Zrušeno.

### Ochrana před duplicitou

Při vytváření i úpravě se kontroluje, zda některý vybraný tým už nemá stejný
zápas v dané sezóně proti témuž soupeři, ve stejném termínu a se stejným
směrem domácí/venku. Pokud ano, uložení se zastaví a oznámení nabídne
**Otevřít existující zápas**. Použijte tento záznam pro nominaci, docházku i výsledky.
Při jiném čase téhož dne formulář nabídne odkazy na podobná utkání. Zkontrolujte,
zda nejde jen o překlep v čase; samostatné utkání lze uložit.

Stejnou kontrolu používá automatický import, včetně souběhu s ručním ukládáním.
Změna termínu na zdroji aktualizuje známé utkání. Existující docházka, statistiky
a pokyny zůstávají navázané na původní zápas; import duplicity automaticky neslučuje
ani nemaže. Různá utkání stejného dne, například turnaj, lze nadále zadat samostatně.

### Nominace a docházka hráčů
Tento proces probíhá ve dvou fázích (Pozvánka a Skutečnost):
- **Nominace**: Trenér v detailu zápasu (záložka Docházka) vybere hráče z týmové soupisky. Pomocí hromadné akce odešle pozvánku.
- **Odpověď hráče**: Hráči v mobilní aplikaci dostanou notifikaci a musí potvrdit účast (`confirmed`) nebo se s omluvou odhlásit (`declined`).
- **Skutečnost (Actual Status)**: Po skončení zápasu trenér upraví docházku na "Byl" (`attended`) nebo "Nebyl" (`absent`).

### Výsledky a statistiky (Reporty)
- **Skóre**: Po zápase zadejte konečné skóre. Systém automaticky vyhodnotí výhru/prohru a zabarví výsledek v tabulce.
- **Body a fauly**: Pokud je zápas synchronizovaný z ČBF, statistiky jsou načteny automaticky. Pokud ne, lze je zapisovat ručně na soupisce zápasu.
- **Odkazy**: U každého zápasu doporučujeme vložit odkaz na "Technický zápis ČBF", aby k němu měli hráči snadný přístup.

### Tipy a řešení problémů
- **Neplatný odkaz na veřejný detail**: Adresa `/zapasy/{id}` vyžaduje číselné ID existujícího zápasu. Neplatný odkaz (např. `/zapasy/null`) nebo smazaný zápas vrací stránku „Nenalezeno“ (HTTP 404).
- **Změna času**: Pokud se čas zápasu změní v ČBF, systém jej při příští synchronizaci sám aktualizuje.
- **Mismatch v docházce**: Pokud hráč potvrdil účast, ale nepřišel, systém v tabulce zápasů zobrazí **červený badge s počtem rozporů** (Mismatches). To je signál pro trenéra, aby docházku prověřil.

### Předzápasová predikce

Procento vždy vyjadřuje šanci našeho týmu, také při zápase venku. V programu, detailu, modalu a na veřejném webu se používá stejná uložená hodnota. Model kombinuje Elo, posledních pět dokončených zápasů, soupisku, externí srovnání a vzájemnou historii. Chybějící statistika není nula ani prohra; nízká důvěra znamená málo podkladů. Výstup je konzervativní odhad v rozsahu 10–90 %, nikoli záruka výsledku.

U venkovních zápasů modal správně přiřazuje hráče, srovnání a bilanci našemu týmu a soupeři. Při nejasném názvu se výsledek z historie neodhaduje. Po odehrání se původní předpověď zachová, import statistik ji nepřepisuje pozdějšími údaji. Chybějící předzápasový snímek se zpětně nevymýšlí.

Oprava či odstranění výsledku přepočítá Elo celé sezóny a zařadí aktualizaci budoucích předpovědí do fronty. Po ručním přepočtu v systémové konzoli proto vyčkejte na dokončení fronty a obnovte stránku; zpráva o naplánování úloh ještě nepotvrzuje nový výsledek. Změna týmu, soupeře, sezóny nebo strany utkání odstraní původní předpověď a u plánovaného zápasu vyvolá nový výpočet.
