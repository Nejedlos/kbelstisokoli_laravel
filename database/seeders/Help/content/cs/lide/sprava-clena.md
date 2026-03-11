Detailní editace člena (Lidé a členové > Uživatelé > vybraný uživatel) je rozdělena do několika logických záložek pro přehlednost a snadnou správu velkého množství dat. V záhlaví vždy vidíte souhrnnou kartu s klíčovými údaji (ID, VS, Status).

### Záložka: Přehled a Identita
Slouží ke správě základní identity a kontaktů.
- **Avatar**: Profilová fotografie. Kliknutím na fotku v záhlaví nebo v záložce lze nahrávat nové verze (např. po focení sezónních portrétů).
- **Zobrazované jméno**: Pokud je vyplněno, systém jej upřednostňuje na webu před kombinací jméno+příjmení (např. pro zkrácenou přezdívku).
- **Telefonní čísla**: Lze zadat hlavní i sekundární kontakt (přidává automaticky prefix +420).

### Záložka: Osobní a Adresa
- **Datum narození**: Klíčový údaj pro automatické zařazování do věkových kategorií.
- **Nouzový kontakt**: Jméno a telefon na osobu, kterou má trenér volat v případě úrazu na tréninku. **Důležité u dětí.**
- **Adresa**: Slouží pro oficiální korespondenci a je vyžadována pro administrativu spojenou s registrací v ČBF a Sokole.

### Záložka: Klubové údaje
- **ID člena a Platební VS**: Unikátní identifikátory v klubu. Pokud u nového člena nejsou vyplněny, lze je vygenerovat ikonou "obnovit" v poli. Jednou vygenerované ID již nelze měnit.
- **Stav členství**: Rozlišení typu "Hráč", "Pasivní člen" nebo "Čestný člen".
- **Finance OK**: Rychlý indikátor (přepínač), zda má člen v pořádku všechny členské závazky (nastavuje se ručně po dohodě nebo automaticky z finančního modulu).
- **Doporučená metoda platby**: Volba mezi převodem, hotovostí atd. (ovlivňuje info pro uživatele v členské sekci).

### Záložka: Hráčské údaje
Tato sekce se zobrazuje pouze pokud je zapnut přepínač "Má aktivní hráčský profil".
- **Basketbal**: Evidence čísla dresu, licence ČBF a herní pozice. Lze zde určit "Primární tým", který se zobrazuje u jména v seznamech.
- **Fyzické parametry**: Výška a váha (pro trenéry) a velikost výstroje (dres/trenýrky) pro objednávky.
- **Interní sekce**: Obsahuje skryté poznámky trenéra a lékařské poznámky (např. alergie, astma, oční vady), které běžný uživatel nevidí.
- **Galerie hráče**: Nahrávání fotografií ze zápasů. První fotografie v gridu je brána jako primární pro soupisku.

### Záložka: Zabezpečení
- **Správa hesla**: Administrátor může nastavit nové heslo (dehydrované, tj. mění se jen při vyplnění).
- **2FA (Dvoufázové ověření)**: Vidíte zde stav zabezpečení uživatele. Pokud se uživatel nemůže přihlásit kvůli ztrátě telefonu, lze zde 2FA resetovat (vypnout).
- **Aktivace účtu**: V záložce Zabezpečení (u Editace) je i akce pro rychlou aktivaci/deaktivaci celého přístupu.

### Záložka: Admin
- **Uživatelské role**: Přidělení práv (Administrátor, Trenér, Editor). Člen klubu může mít více rolí současně.
- **Admin poznámka**: Interní textový prostor pro správce systému (např. historie problémů, poznámky k přestupu).
- **Audit**: Přehled o datech vytvoření, úpravy a posledního přihlášení (last login).

### Časté postupy
- **Vytvoření nového hráče**: Založte uživatele, na záložce Klub vygenerujte ID a VS, na záložce Hráč zapněte "Aktivní hráčský profil" a vyplňte herní pozici.
- **Odchod člena**: Na záložce Klub nastavte "Datum ukončení členství" a na záložce Zabezpečení uživatele deaktivujte.
