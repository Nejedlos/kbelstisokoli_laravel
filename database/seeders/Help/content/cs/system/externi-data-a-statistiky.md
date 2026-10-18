# Externí data a párování hráčů

Systém KS není izolovaný ostrov. Pro automatizaci výsledků, tabulek a statistik se pravidelně spojuje s externími servery (zejména s portálem ČBF). Aby tato synchronizace fungovala, je nutné správně nastavit "Párování".

### Párování entit (Mappings)
Párování je technický most mezi naším systémem a cizím systémem.
- **Týmy:** Musíte propojit náš tým (např. U15) s jeho ID v systému ČBF.
- **Hráči:** Každý hráč, který má mít na webu statistiky, musí mít v sekci "Párování" nastaveno své `external_id` (např. ID z portálu cz.basketball).
- **Zápasy:** Systém automaticky páruje zápasy podle unikátních kódů federace.

### Zdroje statistik (Stat Sources)
V této sekci definujete, odkud systém data stahuje.
- **API URL:** Adresa, na které se data nacházejí.
- **API Key:** Přístupové heslo pro bezpečné stahování dat.
- **Synchronizační intervaly:** Určuje, jak často se mají data aktualizovat (např. každou hodinu v sezóně, jednou denně v létě).

Pražské soutěže na `pbasket.cz.basketball` jsou součástí stejné platformy CZ.BASKETBALL. U týmové sezóny ponechte zdroj `czbasketball` a vyplňte konkrétní týmovou URL, URL seznamu zápasů a URL tabulky soutěže z PBasket.

Pokud chcete zveřejnit a pravidelně aktualizovat pouze tabulku soutěže, ponechte **Aktivní pro synchronizaci** vypnuté a zapněte **Synchronizovat pouze tabulku soutěže**. Tabulka se pak zahrne do ruční i hodinové synchronizace, ale soupisky, hráči a zápasy se z této konfigurace nenačtou. Homepage používá pro tabulky pouze konfigurace povolené jedním z těchto dvou přepínačů v aktivní sezóně.

Pro rozpis, výsledky a statistiky bez zásahů do členství ponechte **Aktivní pro synchronizaci** vypnuté a zapněte **Synchronizovat pouze zápasy a statistiky**. Tento režim nemění soupisku, nezakládá účty, nestahuje fotografie a nevytváří finanční předpisy. Již spárované hráče použije, ostatní zobrazí ve statistice pod jejich jménem. Přepínač tabulky soutěže lze zapnout současně a funguje nezávisle.

U PBasket použijte jako **URL seznamu zápasů** úplnou týmovou stránku pro správnou sezónu, například `https://pbasket.cz.basketball/tym/7761?y=2026`. Samostatný přehled `/zapasy` může zobrazovat jen omezený výběr. Neurčený termín se do rozpisu zatím neimportuje. Podrobné statistiky se stahují až po termínu zápasu; pokud zdroj boxscore ještě nezveřejnil, dřívější statistiky zůstanou zachované. Případné nejednoznačné párování uvidíte v logu importu a režim zápasy automaticky neslučuje.

### Řešení mismatchů (Neshody)
Někdy synchronizace narazí na problém:
- **Jméno nesouhlasí:** Hráč je u nás veden jinak než v ČBF (např. diakritika). Systém nahlásí "Mismatch" a vy musíte ručně potvrdit, že jde o stejnou osobu.
- **Duplicitní ID:** Dva hráči mají stejné externí ID. To je kritická chyba, kterou je nutné opravit v detailu uživatele.

### Manuální vynucení synchronizace
Pokud potřebujete data hned (např. po skončení důležitého zápasu):
1. Přejděte do sekce **Externí zdroje**.
2. Použijte akci **Spustit synchronizaci nyní**.
3. Sledujte průběh v sekci **Logy importů**.

### Časté dotazy
- **Proč se hráči nenačetly body?** Zkontrolujte, zda má hráč nastavené správné `external_id` a zda je jeho tým v dané soutěži spárován.
- **Kde najdu ID hráče v ČBF?** Obvykle v URL adrese profilu hráče na webu cz.basketball.

### Zápas bez zveřejněných statistik
Pokud zdroj ještě nemá tabulku hráčských statistik, zůstane boxscore prázdný a import obsahuje upozornění. Prázdná tabulka sama o sobě neznamená pád synchronizace; údaje lze doplnit při dalším importu.
