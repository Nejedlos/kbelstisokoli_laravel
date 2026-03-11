# UI Audit: Batch 02 – Lidé a členové

Tento dokument obsahuje detailní analýzu skutečného stavu uživatelského rozhraní a technické implementace sekcí souvisejících se správou uživatelů, hráčů a členské základny v projektu Kbelští sokoli.

## 1. Analyzované sekce (Resources & Pages)

Audit vychází z přímé kontroly následujících tříd a souborů:
- **Uživatelé**: `App\Filament\Resources\Users\UserResource` (a související `UserForm`, `UsersTable`)
- **Hráčské profily**: `App\Filament\Resources\PlayerProfiles\PlayerProfileResource`
- **Leady / Zájemci**: `App\Filament\Resources\Leads\LeadResource`
- **Role**: `App\Filament\Resources\Roles\RoleResource`
- **Oprávnění**: `App\Filament\Resources\Permissions\PermissionResource`
- **Sezónní konfigurace**: `App\Filament\Resources\UserSeasonConfigs\UserSeasonConfigsResource`

---

## 2. Sekce: Uživatelé (`UserResource`)

Hlavní a nejkomplexnější sekce pro správu členské základny.

- **Název v menu**: `Uživatelé` (Skupina: `Lidé a členové`)
- **Ikona**: `fa-light fa-users`
- **Cílové role**: `admin`, `super_admin`

### 2.1 Tabulka uživatelů (`UsersTable`)
- **Sloupce**:
    - **Avatar**: Kruhový náhled, toggleable.
    - **Jméno**: Obsahuje Email a sady stavových ikon:
        - `fa-circle-exclamation` (žlutá): Indikuje duplicity (stejné jméno v DB).
        - `fa-ghost` (šedá): Indikuje dočasný "Ghost" profil (bez e-mailu/přihlášení).
        - `fa-cloud-arrow-down` (modrá): Indikuje synchronizaci z cz.basketball.
    - **ID člena**: Unikátní identifikátor v klubu (`club_member_id`).
    - **VS platby**: Variabilní symbol (`payment_vs`).
    - **Role**: Badge s názvy rolí uživatele.
    - **Status členství**: Badge (např. Aktivní člen, Hostování, Přerušené).
    - **#**: Číslo dresu (pokud má hráčský profil).
    - **Primární tým**: Název týmu.
    - **Aktivní**: Checkbox ikonka (is_active).
    - **Aktivita**: Datum a čas posledního přihlášení.
- **Filtry**:
    - Role, Status členství, Typ členství, Jazyk (CS/EN), Aktivní účet, Stav 2FA, Má hráčský profil, Pohlaví, Duplicity.
- **Akce záznamu**:
    - `Zobrazit duplicity`: Vyfiltruje tabulku na stejné jméno.
    - `Odeslat pozvánku`: Zašle e-mail pro nastavení hesla (pouze pro aktivní, ne-onboardované).
    - `Převzít identitu (Impersonate)`: Přihlášení za uživatele (pouze pro adminy).
    - `Sloučit s...`: Manuální sloučení s jiným uživatelem (převod dat).
    - `Synchronizovat z cz.basketball`: Manuální stažení dat hráče.
- **Hromadné akce**:
    - `Sloučit Ghosty automaticky`: Hromadné spojení dočasných profilů s reálnými uživateli.
    - `Aktivovat / Deaktivovat`: Hromadná změna stavu účtu.

### 2.2 Formulář uživatele (`UserForm`)
Rozdělen do 6 tematických záložek (Tabs):
1.  **Přehled (Overview)**:
    - Editace jména, příjmení, emailu a telefonů.
    - Interaktivní správa avataru (včetně galerie).
2.  **Osobní údaje (Personal)**:
    - Datum narození, pohlaví, národnost, adresa.
    - Nouzový kontakt (jméno a telefon).
3.  **Klub (Club)**:
    - Správa ID člena a VS (včetně tlačítek pro automatické generování).
    - Status a typ členství, data platnosti členství.
    - Platební údaje (Finance OK toggle, metoda, poznámka).
4.  **Hráč (Player)**:
    - Toggle "Má aktivní hráčský profil" (dynamicky zobrazuje další pole).
    - Číslo dresu, pozice, hand, licence, primární tým.
    - Fyzické parametry (výška, váha, velikosti dresu/kraťasů).
    - Poznámky (zdravotní, trenérská).
    - Galerie fotografií hráče (pro soupisky).
5.  **Zabezpečení (Security)**:
    - Změna hesla.
    - Stav 2FA (Aktivní/Čeká/Neaktivní) s možností resetu administrátorem.
    - Status aktivity účtu.
6.  **Admin**:
    - Přiřazení systémových rolí.
    - Interní poznámka admina.
    - Audit logy (data vytvoření/úpravy).

---

## 3. Sekce: Hráčské profily (`PlayerProfileResource`)

Doplňkový náhled zaměřený čistě na sportovní data.

- **Název v menu**: `Hráčské profily`
- **Ikona**: `fa-light fa-user-vneck`
- **Zobrazení**: Tabulka zobrazuje Jméno, Dres #, Pozici, Týmy a Aktivní status.
- **Filtry**: Podle týmů a aktivity.
- **Vazba**: 1:1 k Uživateli. Většina dat se zrcadlí do `UserForm` záložky "Hráč".

---

## 4. Sekce: Leady / Zájemci (`LeadResource`)

Lidé, kteří se přihlásili přes webové formuláře (nábor, kontakt).

- **Název v menu**: `Leady / Zájemci` (Skupina: `Obsah a média`)
- **Ikona**: `fa-light fa-bullhorn`
- **Pole**: Jméno, Email, Typ leadu (např. Nábor), Status (Nový, Zpracovaný).
- **Vztah**: Potenciální budoucí uživatelé.

---

## 5. Sekce: Role a Oprávnění

Technické nastavení přístupů do systému.

- **Role**: `admin`, `coach`, `editor`, `parent`, `player`, `super_admin`.
- **Oprávnění**:
    - `access_admin`: Vstup do administrace.
    - `impersonate_users`: Možnost "vlézt" do profilu jiného uživatele.
    - `manage_users`: Kompletní správa uživatelské základny.
    - `view_member_section`: Přístup do členské sekce pro hráče/rodiče.
    - `manage_attendance`: Správa docházky (pro trenéry).

---

## 6. Rodinné vazby (Parents & Children)

Implementováno jako Relation Managery u uživatele.

- **Typ vztahu**: Rodič, Zákonný zástupce, Dítě.
- **Metadata vazby**:
    - Nouzový kontakt (Ano/Ne).
    - Fakturační kontakt (Ano/Ne) – určuje, komu se posílají maily o platbách.
    - Preferovaný kanál (Email, SMS, WhatsApp).
- **Funkčnost**: Umožňuje provázat např. tátu se dvěma dětmi, přičemž táta vidí data dětí ve své členské sekci.

---

## 7. Návrh témat pro články (Batch 02)

1.  **Evidence uživatelů**: Přidávání členů, generování ID a VS, správa statusů.
2.  **Hráčské profily**: Detailní nastavení sportovních dat, čísel dresů a licencí.
3.  **Rodinné vazby**: Jak propojit rodiče s dítětem a nastavit fakturační/nouzové kontakty.
4.  **Slučování a duplicity**: Jak řešit Ghost profily a čistit databázi členů.
5.  **Role a oprávnění**: Kdo co v systému vidí a jak nastavit přístupy trenérům či redaktorům.
6.  **Zájemci o nábor**: Jak zpracovat přihlášky z webu a převést je na členy.

---

## 8. Nejasnosti k ověření

1.  **Automatický převod Lead -> User**: Existuje v UI tlačítko pro "Vytvořit uživatele z leadu", nebo se to dělá ručně?
2.  **Hromadné generování VS**: Lze vygenerovat VS hromadně pro celou kategorii, nebo pouze individuálně v editaci? (Audit ukazuje pouze individuální Action v poli).
3.  **PWA Instalace**: Je návod na PWA součástí sekce Onboarding, nebo patří k uživatelskému profilu v Batch 02? (Audit Batch 01 jej zahrnul jako téma).

---
*Zpracoval: Junie (AI Assistant)*
