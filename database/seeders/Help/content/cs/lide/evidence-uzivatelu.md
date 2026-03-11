Tato sekce (Lidé a členové > Uživatelé) je základním kamenem správy celé členské základny. Slouží k rychlému vyhledávání členů, kontrole jejich aktivního stavu a provádění hromadných operací.

### Přehled tabulky a vizuální indikátory
V hlavní tabulce vidíte všechny registrované osoby. Systém automaticky analyzuje data a upozorňuje na důležité stavy pomocí ikon v řádku jména:

- **<i class="fa-light fa-ghost text-gray-400"></i> Ikona ducha (Ghost)**: Značí uživatele, který byl v systému vytvořen (např. importem), ale ještě se nikdy nepřihlásil a nedokončil proces "onboardingu". Tyto profily jsou často duplicitní k reálným účtům.
- **<i class="fa-light fa-circle-exclamation text-warning"></i> Ikona vykřičníku**: Indikuje nalezenou duplicitu. Systém našel jiný záznam se stejným jménem. Číslo v závorce udává počet nalezených duplicit.
- **<i class="fa-light fa-cloud-arrow-down text-info"></i> Ikona cloudu**: Označuje uživatele, jehož data jsou synchronizována z externího zdroje (např. cz.basketball).
- **Status Aktivní/Neaktivní**: Barevný odznak určující, zda má uživatel aktuálně přístup do své členské sekce. Neaktivní uživatel se nemůže přihlásit.

### Pokročilá filtrace
Pro efektivní správu tisíců členů využívejte kombinované filtry:

1. **Dle Role**: Zobrazte si pouze "Trenéry", "Administrátory" nebo "Hráče".
2. **Dle Týmu**: Klíčový filtr pro trenéry. Zobrazí členy přiřazené ke konkrétnímu týmu v aktuální sezóně.
3. **Stav 2FA**: Umožňuje najít uživatele, kteří ještě nemají aktivní dvoufázové ověření (kritické pro administrátory).
4. **Hráčský profil**: Rychle odfiltruje osoby, které jsou v systému pouze jako doprovod (rodiče) nebo funkcionáři bez herní historie.
5. **Duplicity**: Speciální filtr, který skryje všechny unikátní záznamy a ponechá pouze ty, kde existuje shoda jména – ideální pro pročištění databáze.

### Hromadné operace (Bulk Actions)
V levé části tabulky můžete zaškrtnout více uživatelů a použít hromadné akce v záhlaví:

- **Aktivovat / Deaktivovat**: Hromadné zapnutí/vypnutí přístupů (např. po skončení sezóny).
- **Synchronizovat z cz.basketball**: Hromadná aktualizace statistik a licencí pro vybrané hráče.
- **Sloučit Ghosty automaticky**: Bezpečné hromadné sloučení dočasných profilů s jejich reálnými protějšky (pokud je shoda jména 1:1).

### Klíčové administrativní akce
- **Sloučit s...**: Pokud najdete duplicitu, touto akcí převedete všechna data (statistiky, platby, vazby) na jeden hlavní profil a druhý (nadbytečný) smažete. **Pozor: Tato operace je nevratná.**
- **Zobrazit duplicity**: Rychlá akce, která vás přepne do vyhledávání všech osob se stejným jménem, abyste je mohli porovnat před sloučením.
- **Poslat pozvánku**: Odešle e-mail s odkazem na nastavení hesla. Používejte u nových členů nebo při resetu přístupu.

### Dopady a vazby
Jakákoli změna stavu uživatele v této sekci má okamžitý řetězový vliv:

- **Přístup**: Deaktivace uživatele okamžitě zruší všechna jeho aktivní přihlášení (včetně mobilní aplikace).
- **Soupisky a nominace**: Neaktivní uživatelé nebo uživatelé bez hráčského profilu se nenabízejí v seznamech pro zápasy a tréninky.
- **Finanční modul**: Deaktivací se zastaví automatické generování nových platebních předpisů.
