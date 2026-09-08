# Nábory a správa zájemců (Leady)

Tento modul slouží k evidenci a zpracování nových zájemců o basketbal, kteří kontaktují klub prostřednictvím webového formuláře "Chci hrát".

### Co je to Lead?
Lead je potenciální člen. V systému se zobrazuje v sekci **Lidé a členové > Nábory**.
- Obsahuje základní údaje: jméno dítěte, rok narození, kontakt na rodiče a případnou zprávu.
- **Důležité:** Lead ještě není uživatelem systému. Nemá přístup do členské sekce a nelze mu vystavovat předpisy.

### Workflow zpracování zájemce
Doporučený postup pro náborového manažera:
1. **Nový:** Lead se objeví v seznamu se stavem "Nový". Pokud je nastavená výchozí odpovědná osoba, přijde jí ihned e-mail s kontaktem; jinak jde upozornění technickému kontaktu.
2. **Přiřazení a kontaktování:** V detailu zvolte odpovědnou osobu (při změně dostane upozornění) a stav **V řešení**. Manažer zavolá nebo napíše rodičům.
3. **Zkušební trénink:** Pokud zájemce přijde na trénink, můžete si k leadu psát interní poznámky (např. "Šikovný, chce to zkusit").
4. **Rozhodnutí:**
    - **Přijat:** Pokud se zájemce rozhodne pokračovat, označte lead jako **Přijat** a ručně vytvořte profil v sekci Uživatelé.
    - **Nepřijat:** Lead označíte jako **Nepřijat**.
    - **Odloženo:** Pokud je potřeba se ozvat později, označte lead jako **Odloženo** a nastavte další kontakt.

### Převod na člena (Důležité)
Systém z bezpečnostních a administrativních důvodů nenabízí automatický převod "1-click convert" z Leadu na Uživatele.
- **Důvod:** Při zakládání člena je potřeba vyplnit mnohem více údajů (adresa, pojišťovna, GDPR souhlas), které v úvodním formuláři nejsou.
- **Postup:** Otevřete si Lead v jednom okně a v druhém okně vytvořte nového Uživatele. Po vytvoření uživatele můžete Lead v systému smazat nebo archivovat.

### Statistiky náborů
V přehledu náborů můžete filtrovat podle roku narození nebo podle sezóny. To vám pomůže naplánovat kapacity jednotlivých přípravek.

### Časté dotazy
- **Může lead vyplnit rodič?** Ano, formulář na webu je koncipován tak, aby jej vyplňoval zákonný zástupce.
- **Chodí mi notifikace na nový lead?** Ano, odpovědné osobě přijde e-mail o každém novém zájemci. Pokud není odpovědná osoba nastavená, e-mail dostane technický kontakt.
