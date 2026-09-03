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
