# UI Audit: Batch 03 – Sportovní agenda

Tento dokument obsahuje detailní analýzu skutečného stavu uživatelského rozhraní a technické implementace pro moduly sportovní agendy v projektu Kbelští sokoli.

## 1. Přehled analyzovaných sekcí

| Sekce v menu | Resource / Page Class | Cílové role | Hlavní účel |
| :--- | :--- | :--- | :--- |
| **Týmy** | `TeamResource` | Admin, Coach | Správa kategorií, soupisek a trenérů. |
| **Sezóny** | `SeasonResource` | Admin, Superadmin | Definice aktivního období a překlápění dat. |
| **Zápasy** | `BasketballMatchResource` | Admin, Coach | Rozpis utkání, nominace a výsledky. |
| **Soupeři** | `OpponentResource` | Admin, Coach | Adresář klubů, se kterými hrajeme. |
| **Tréninky** | `TrainingResource` | Admin, Coach | Plánování tréninků a evidence docházky. |
| **Hráčský profil** | `UserResource` (Tab Hráč) | Admin, Coach | Detailní sportovní a zdravotní evidence. |

---

## 2. Detailní rozbor sekcí

### A. Týmy (`teams`)
- **Menu Path**: Sportovní agenda > Týmy
- **Resource**: `App\Filament\Resources\Teams\TeamResource`
- **Hlavní prvky**:
    - **Tabulka**: Název, Kategorie (U11, U13, Muži...), Barva (HEX), Hlavní trenér, Počet hráčů.
    - **Filtry**: Podle kategorie, podle sezóny.
    - **Akce (Row)**: Upravit, Duplikovat.
    - **Akce (Header)**: `syncFromCzBasketball` – Synchronizace týmu a soupisky z portálu cz.basketball (vyžaduje externí ID).
- **Formulář (Create/Edit)**:
    - **Základní**: Název, Slug, Kategorie (Select), Sezóna (Select), Barva (ColorPicker).
    - **Trenéři**: Multiple Select (vztah `coaches`).
    - **Externí**: `external_id` (pro synchronizaci s cz.basketball).
- **Relation Managers**:
    - **Hráči (`PlayersRelationManager`)**: Tabulka hráčů v týmu. Obsahuje příznaky `is_primary_team`, `is_on_roster` a roli v týmu.

### B. Sezóny (`seasons`)
- **Menu Path**: Sportovní agenda > Sezóny
- **Resource**: `App\Filament\Resources\Seasons\SeasonResource`
- **Hlavní prvky**:
    - **Tabulka**: Název (2024/2025), Aktivní (Badge), Počet týmů.
    - **Akce (Header)**: `initializeConfigurations` – Kritická akce pro start sezóny (vytvoří `UserSeasonConfig` pro všechny členy na základě předchozí sezóny).
- **Formulář**: Název, Start, Konec, Příznak `is_active`.

### C. Zápasy a Nominace (`basketball-matches`)
- **Menu Path**: Sportovní agenda > Zápasy
- **Resource**: `App\Filament\Resources\BasketballMatches\BasketballMatchResource`
- **Hlavní prvky**:
    - **Tabulka**: Tým, Soupeř, Datum a čas, Místo, Skóre (pokud je po zápase), Počet omluv.
    - **Filtry**: Podle týmu, podle období.
- **Relation Managers**:
    - **Nominace/Docházka (`AttendancesRelationManager`)**:
        - **Stavy (Hráč)**: `pending`, `confirmed`, `declined`, `maybe`.
        - **Stavy (Trenér)**: `attended`, `absent`, `excused`.
        - **Mismatch**: Barevné zvýraznění rozdílu (Hráč slíbil, ale nepřišel).
        - **Hromadné akce**: Odeslat pozvánku na zápas (email/push).

### D. Tréninky (`trainings`)
- **Menu Path**: Sportovní agenda > Tréninky
- **Resource**: `App\Filament\Resources\Trainings\TrainingResource`
- **Hlavní prvky**:
    - **Tabulka**: Týmy (Badges), Místo, Začátek, Konec, Počet "Mismatches".
    - **Zobrazení**: Budoucí tréninky zvýrazněny zeleně, minulé šedě.
- **Formulář**: Výběr více týmů, Lokace (Hala Kbely), Časy, Poznámka pro hráče (Program).
- **Relation Managers**: `AttendancesRelationManager` (stejný jako u zápasů).

### E. Soupeři (`opponents`)
- **Menu Path**: Sportovní agenda > Soupeři
- **Resource**: `App\Filament\Resources\Opponents\OpponentResource`
- **Hlavní prvky**:
    - **Tabulka**: Název, Město, Logo.
    - **Widget**: `OpponentMergeSuggestionsWidget` – Navrhuje sloučení duplicitních soupeřů (např. "BC Slaný" vs "Slaný").

### F. Sportovní evidence v profilu (`user_player_tab`)
- **Umístění**: Lidé a členové > Uživatelé > Detail > Tab "Hráč"
- **Obsah**:
    - **Basketbalové údaje**: Číslo dresu (aktuální a preferované), Pozice (PG-C), Dominantní ruka, Číslo licence ČBF.
    - **Týmové údaje**: Primární tým, datum vstupu do týmu.
    - **Fyzické údaje**: Výška (cm), Váha (kg), Velikost dresu a trenýrek.
    - **Interní**: Lékařská poznámka, Poznámka trenéra (skryto pro hráče).
    - **Média**: Galerie fotografií hráče (pořadí určuje primární fotku na soupisku).

---

## 3. Vazby a Workflow

1.  **Start sezóny**: Superadmin založí sezónu -> Spustí "Inicializaci konfigurací" -> Trenéři v Týmech přiřadí hráče na soupisky pro novou sezónu.
2.  **Organizace zápasu**: Trenér vytvoří zápas -> Vybere soupisku týmu -> Hráči v mobilu vidí "Nominaci" -> Potvrdí účast -> Po zápase trenér zapíše realitu (kdo skutečně hrál).
3.  **Synchronizace z cz.basketball**: Pokud má tým `external_id`, jedním kliknutím se dotáhnou zápasy a soupiska přímo z oficiálního webu federace.
4.  **Docházkový automat**: Systém sleduje rozdíl mezi `confirmed` a `attended` (tzv. Mismatch), což slouží k hodnocení disciplíny.

---

## 4. Problémová a nejasná místa (k ověření)

- **Opakování tréninků**: V UI chybí explicitní "Recurrence" (např. každé úterý). Je potřeba ověřit, zda se tréninky nekopírují hromadnou akcí v tabulce.
- **Výpůjčky dresů**: V kódu je vidět pole pro dresy, ale neexistuje dedikovaná tabulka pro "Sklad / Výpůjčky". Pravděpodobně se to zatím řeší textovou poznámkou.
- **Lékařské prohlídky**: V DB je `medical_note`, ale chybí pole pro "Datum konce platnosti prohlídky". Ověřit, zda se neplánuje.
- **Omlouvání**: Rozdíl mezi `declined` (hráč se omluvil dopředu) a `excused` (trenér ho omluvil dodatečně) může být pro nové uživatele matoucí.
