# Návrh článků: Batch 02 – Lidé a členové

Tento dokument definuje seznam a strukturu help článků pro druhou obsahovou dávku, která se zaměřuje na správu uživatelské základny, hráčských profilů, náborů a rolí.

## Přehled navržených článků

| ID | Title | Slug | Audience | Typ |
|:---|:---|:---|:---|:---|
| 1 | Evidence uživatelů a členů | `evidence-uzivatelu` | Admin, Superadmin | Onboarding / Sekční |
| 2 | Přidání a správa člena | `sprava-clena` | Admin, Superadmin | Detailní |
| 3 | Klubové údaje a stav člena | `klubove-udaje-a-stav` | Admin, Superadmin | Detailní |
| 4 | Rodiny a rodinné vazby | `rodiny-a-vazby` | Admin, Superadmin | Detailní |
| 5 | Hráčské profily a historie | `hracske-profily` | Admin, Coach, Superadmin | Detailní |
| 6 | Zpracování zájemců (Nábory) | `zpracovani-nabory` | Admin, Editor, Superadmin | Detailní |
| 7 | Role a přístupová práva | `role-a-opravneni` | Superadmin | Technický |

---

## Detailní specifikace článků

### 1. Evidence uživatelů a členů
- **Slug**: `evidence-uzivatelu`
- **Audience**: `admin`, `super_admin`
- **Návazná sekce**: Lidé a členové > Uživatelé
- **UI stránky**: `UserResource` (List), `UsersTable`
- **Hlavní účel**: Základní orientace v seznamu všech osob v systému, vyhledávání a filtrování podle stavu či rolí.
- **Časté akce**:
    - Vyhledání uživatele podle jména nebo e-mailu.
    - Filtrace aktivních/neaktivních členů.
    - Identifikace "Ghost" profilů (duplicity bez e-mailu).
    - Hromadný export kontaktů.
- **Riziková místa**: Nechtěné smazání uživatele (místo deaktivace), přehlédnutí duplicitních profilů.
- **Related sections**: `sprava-clena`, `klubove-udaje-a-stav`, `exporty-dat` (Batch 03).
- **Search keywords**: seznam lidí, databáze členů, vyhledávání uživatele, export kontaktů, ghost profily.

### 2. Přidání a správa člena
- **Slug**: `sprava-clena`
- **Audience**: `admin`, `super_admin`
- **Návazná sekce**: Lidé a členové > Uživatelé > Nový uživatel
- **UI stránky**: `UserResource` (Create / Edit - Tab Osobní)
- **Hlavní účel**: Postup při ručním založení nového uživatele a úpravě jeho základních identifikačních údajů.
- **Časté akce**:
    - Vyplnění povinných polí (Jméno, Příjmení, E-mail).
    - Nastavení data narození (klíčové pro zařazení do kategorií).
    - Nahrání profilové fotografie (avataru).
    - Úprava kontaktních údajů (telefon, adresa).
- **Riziková místa**: Chybné zadání e-mailu (znemožní přihlášení), duplicitní registrace.
- **Related sections**: `evidence-uzivatelu`, `rodiny-a-vazby`.
- **Search keywords**: nový člen, přidat uživatele, osobní údaje, úprava profilu, registrace člena, avatar.

### 3. Klubové údaje a stav člena
- **Slug**: `klubove-udaje-a-stav`
- **Audience**: `admin`, `super_admin`
- **Návazná sekce**: Lidé a členové > Uživatelé > Editace (Tab Klub, Hráč)
- **UI stránky**: `UserResource` (Edit - Tab Klub, Hráč, Zabezpečení)
- **Hlavní účel**: Správa specifických klubových atributů, jako je status člena (aktivní/neaktivní) a propojení s matricí klubu.
- **Časté akce**:
    - Aktivace / Deaktivace uživatelského účtu (`is_active`).
    - Nastavení ID člena z matriky ČBF/Sokola.
    - Kontrola stavu 2FA (Zabezpečení).
    - Označení hráče jako "externí synchronizace".
- **Riziková místa**: Deaktivace uživatele, který potřebuje přístup do členské sekce, nekonzistence s externí matricí.
- **Related sections**: `evidence-uzivatelu`, `hracske-profily`.
- **Search keywords**: aktivní člen, deaktivace uživatele, klubové ID, členské číslo, id hráče, 2FA stav.

### 4. Rodiny a rodinné vazby
- **Slug**: `rodiny-a-vazby`
- **Audience**: `admin`, `super_admin`
- **Návazná sekce**: Lidé a členové > Uživatelé > Editace (Relation Managers)
- **UI stránky**: `UserResource` (Relation Managers: Rodiče, Děti)
- **Hlavní účel**: Vysvětlení systému propojení rodičů s dětmi pro usnadnění správy profilů a plateb (rodič vidí údaje dětí v aplikaci).
- **Časté akce**:
    - Propojení existujícího rodiče k dítěti.
    - Nastavení fakturačního kontaktu rodiny.
    - Evidence nouzových kontaktů (v případě úrazu).
    - Odpojení vazby při osamostatnění hráče.
- **Riziková místa**: Chybějící nouzový kontakt u dětí, nejasnost v tom, kdo platí příspěvky (fakturační vazba).
- **Related sections**: `sprava-clena`, `finance-rodina` (Batch 04).
- **Search keywords**: rodič-dítě, rodinná vazba, nouzový kontakt, propojení účtů, fakturační adresa, sourozenec.

### 5. Hráčské profily a historie
- **Slug**: `hracske-profily`
- **Audience**: `admin`, `coach`, `super_admin`
- **Návazná sekce**: Lidé a členové > Hráčské profily
- **UI stránky**: `PlayerProfileResource` (List, Edit)
- **Hlavní účel**: Správa "stintů" (působení hráče v týmech napříč sezónami), evidence čísel dresů a historie výkonů.
- **Časté akce**:
    - Přidání nového působení (tým + sezóna).
    - Změna čísla dresu hráče pro danou sezónu.
    - Ukončení působení v týmu.
    - Přehled historie hráče v klubu.
- **Riziková místa**: Překrývající se období v různých týmech, chybné přiřazení čísla dresu (vliv na zápisy o utkání).
- **Related sections**: `klubove-udaje-a-stav`, `sprava-tymu` (Batch 02 - Sport).
- **Search keywords**: historie hráče, dresy, čísla dresů, stinty, působení v týmu, kariéra.

### 6. Zpracování zájemců (Nábory)
- **Slug**: `zpracovani-nabory`
- **Audience**: `admin`, `editor`, `super_admin`
- **Návazná sekce**: Obsah a média > Zájemci o členství
- **UI stránky**: `LeadResource` (Manage page)
- **Hlavní účel**: Evidence lidí, kteří projevili zájem o členství přes webový formulář "Chci hrát".
- **Časté akce**:
    - Kontrola nových zájemců.
    - Změna stavu leadu (Nový -> Kontaktován -> Přijat/Odmítnut).
    - Manuální překlopení leadu na uživatele (po náboru).
- **Riziková místa**: Zapomenutí na leada bez odpovědi, duplicitní údaje v systému.
- **Related sections**: `sprava-clena`, `naborovy-formular` (Batch 05 - Web).
- **Search keywords**: nábor, nový zájemce, lead, přihláška, chci hrát, náborový formulář.

### 7. Role a přístupová práva
- **Slug**: `role-a-opravneni`
- **Audience**: `super_admin`
- **Návazná sekce**: Lidé a členové > Role / Oprávnění
- **UI stránky**: `RoleResource`, `PermissionResource`
- **Hlavní účel**: Technická správa přístupových úrovní do administrace.
- **Časté akce**:
    - Vytvoření nové role (např. Pomocný trenér).
    - Přiřazení specifických oprávnění (např. `manage_content`) k roli.
    - Kontrola, kdo má roli `admin`.
- **Riziková místa**: Neopatrné přidělení kritických oprávnění (např. smazání dat), nekonzistence v názvosloví.
- **Related sections**: `system-nastaveni` (Batch 06).
- **Search keywords**: oprávnění, role, přístupová práva, permissions, administrátoři, zabezpečení.
