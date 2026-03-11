Sekce **Zápasy** pokrývá kompletní životní cyklus utkání – od automatického rozpisů až po zápis podrobných statistik a výsledků.

### Automatizace: Synchronizace s cz.basketball (ČBF)
Tato funkce je pro klub Kbelští sokoli zásadní a šetří hodiny administrativní práce.
- **Jak to funguje**: Pokud tým hraje soutěž zastřešenou ČBF, systém se automaticky napojuje na jejich API a pravidelně stahuje rozpis i výsledky zápasů.
- **Externí indikátor**: V tabulce zápasů poznáte synchronizovaný zápas podle modré <i class="fa-light fa-cloud-arrow-down text-info"></i> **Ikony cloudu** u datumu zápasu.
- **Hromadná AI synchronizace**: Pokud u historických zápasů chybí detaily (body, fauly hráčů), lze v hromadných akcích vybrat zápasy a spustit "AI Synchronizaci detailů". Ta se pokusí automaticky "vyčíst" a přiřadit statistiky k hráčům v databázi.

### Plánování a správa zápasu
U přátelských utkání nebo turnajů, které nejsou v ČBF, musíte zápas vytvořit ručně.
1. **Základní údaje**: Výběr domácího týmu, soupeře (pokud chybí, založte jej v sekci Soupeři), data, času a místa.
2. **Místo konání (Hala)**: U každého zápasu lze specifikovat konkrétní halu. Pokud je to hala Kbely, automaticky se nabízí s mapou.
3. **Status zápasu**: Plánováno (vytvořeno), Naplánováno (potvrzen termín), Odehráno (zapsán výsledek), Odloženo/Zrušeno.

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
- **Změna času**: Pokud se čas zápasu změní v ČBF, systém jej při příští synchronizaci sám aktualizuje.
- **Mismatch v docházce**: Pokud hráč potvrdil účast, ale nepřišel, systém v tabulce zápasů zobrazí **červený badge s počtem rozporů** (Mismatches). To je signál pro trenéra, aby docházku prověřil.
