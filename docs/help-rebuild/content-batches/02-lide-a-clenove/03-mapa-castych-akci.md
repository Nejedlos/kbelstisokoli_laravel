# Mapa častých akcí - Batch 02: Lidé a členové

Tento dokument mapuje reálné uživatelské postupy v sekci "Lidé a členové" na základě technické implementace ve Filament administraci. Slouží jako podklad pro tvorbu konkrétních návodů v nápovědě.

## 1. Evidence a správa uživatelů (UserResource)

### A. Vytvoření nového člena (Ručně)
*   **UI umístění:** `Uživatelé` -> Tlačítko `Nový uživatel` (v záhlaví tabulky).
*   **Kdo provádí:** Administrátor, Superadmin.
*   **Účel:** Přidání nového hráče, trenéra nebo rodiče do systému mimo automatické importy.
*   **Předpoklady:** Znalost e-mailu a jména osoby.
*   **Kroky:** 
    1. Vyplnění základních údajů (Jméno, Příjmení, E-mail).
    2. Nastavení hesla (nebo nechat prázdné pro automatické odeslání pozvánky později).
    3. Přiřazení základních rolí (např. `player`, `parent`).
*   **Časté chyby:** Překlep v e-mailu (znemožní přihlášení), nepřiřazení role (uživatel nic neuvidí).
*   **Dopad:** Uživatel získá přístup k členské sekci.

### B. Odeslání pozvánky do systému
*   **UI umístění:** `Uživatelé` -> Řádková akce `Poslat pozvánku` (pod ikonou letadélka).
*   **Kdo provádí:** Administrátor.
*   **Účel:** Zaslání e-mailu s odkazem na nastavení hesla uživateli, který se ještě nikdy nepřihlásil.
*   **Předpoklady:** Uživatel musí být aktivní a nesmí mít dokončený onboarding.
*   **Kroky:** Kliknutí na akci -> Potvrzení v modálním okně.
*   **Dopad:** Odeslání systémové notifikace `UserInvitationNotification`.

### C. Sloučení duplicitních profilů
*   **UI umístění:** 
    *   `Uživatelé` -> Řádková akce `Sloučit s...` (pro jednotlivce).
    *   `Uživatelé` -> Hromadná akce `Sloučit identifikované Ghosty`.
*   **Kdo provádí:** Administrátor, Superadmin.
*   **Účel:** Vyčištění databáze od "Ghost" profilů (vytvořených např. importem zápasů) a jejich spojení s reálnými účty.
*   **Průběh:** Vybere se "Cílový uživatel" (ten, který zůstane). Systém převede všechny statistiky, vazby a mapování na něj. Původní (zdrojový) uživatel je smazán.
*   **Rizika:** **Operace je nevratná.** Špatné sloučení může poškodit historické statistiky.

---

## 2. Sportovní agenda člena (Relation Managers)

### E. Přiřazení uživatele k týmu pro sezónu
*   **UI umístění:** `Uživatelé` -> Editace -> Tabulka `Sezónní konfigurace`.
*   **Kdo provádí:** Administrátor.
*   **Účel:** Definice toho, v jakém týmu uživatel v dané sezóně působí, jaké má číslo dresu a jakou roli (Hráč/Trenér).
*   **Kroky:** `Přidat` -> Výběr sezóny -> Výběr týmu -> Zadání čísla dresu -> Výběr role v týmu.
*   **Dopad:** 
    *   **Sport:** Uživatel se objeví na soupisce týmu.
    *   **Finance:** Na základě této konfigurace může systém generovat předpisy plateb (tarify).

### F. Propojení Rodič - Dítě
*   **UI umístění:** `Uživatelé` -> Editace -> Tabulka `Rodiče` (u dítěte) nebo `Děti` (u rodiče).
*   **Kdo provádí:** Administrátor.
*   **Účel:** Umožnit rodiči spravovat docházku a finance svého dítěte v členské sekci.
*   **Předpoklady:** Oba uživatelé musí již v systému existovat.
*   **Dopad:** Rodič uvidí v členské sekci přepínač profilů.

---

## 3. Hráčské profily a nábory

### G. Úprava veřejného profilu hráče
*   **UI umístění:** `Hráčské profily` -> Editace.
*   **Kdo provádí:** Administrátor, Redaktor.
*   **Účel:** Správa informací, které se zobrazují na veřejném webu (biografie, odkazy na statistiky, přezdívka).
*   **Dopad:** Okamžitá aktualizace detailu hráče na webu.

### H. Zpracování zájemce o nábor (Lead)
*   **UI umístění:** `Zájemci / Leady` -> Editace.
*   **Kdo provádí:** Administrátor, Trenér (pokud má přístup k náborům).
*   **Účel:** Evidence lidí, kteří vyplnili formulář na webu.
*   **Workflow:**
    1. Kontrola údajů (věk, kontakt).
    2. Změna stavu na "Kontaktován" / "Na zkoušce".
    3. **Pozor:** Pokud se zájemce stane členem, musí se v sekci `Uživatelé` vytvořit ručně (přímý převod není v UI implementován).
    4. Následná archivace leadu.

---

## 4. Role a oprávnění

### I. Přidělení administrátorských práv
*   **UI umístění:** `Uživatelé` -> Editace -> Tab `Admin` -> Pole `Role`.
*   **Kdo provádí:** Superadmin.
*   **Rizika:** Přidělení role `admin` dává uživateli přístup k citlivým datům (GDPR) a financím celého klubu.
*   **Dopad:** Uživatel po příštím přihlášení uvidí nové sekce v levém menu.
