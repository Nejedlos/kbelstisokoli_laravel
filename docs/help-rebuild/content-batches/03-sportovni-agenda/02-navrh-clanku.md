# Návrh help článků: Batch 03 – Sportovní agenda

Tento návrh definuje konkrétní články nápovědy, které budou vytvořeny pro sekci Sportovní agenda. Každý článek vychází z reálného UI auditu.

## Seznam článků

### 1. Správa týmů a kategorií
- **Slug**: `sprava-tymu`
- **Audience**: `admin`, `coach`
- **Typ**: Detailní sekční článek
- **Účel**: Vysvětlit, jak vytvořit tým, přiřadit barvu a trenéry.
- **Hlavní akce**: Synchronizace z cz.basketball, Duplikace týmu.
- **Related**: `soupisky-a-clenstvi`, `planovani-sezony`.

### 2. Soupisky a členství v týmu
- **Slug**: `soupisky-a-clenstvi`
- **Audience**: `admin`, `coach`
- **Typ**: Detailní sekční článek
- **Účel**: Jak přidat hráče do týmu, nastavení primárního týmu a statusu "na soupisce".
- **Hlavní akce**: Přidat hráče (Attach), Odebrat hráče (Detach).
- **Related**: `sprava-tymu`, `hracske-profily`.

### 3. Plánování a start nové sezóny
- **Slug**: `planovani-sezony`
- **Audience**: `admin`, `super_admin`
- **Typ**: Onboarding / Procesní článek
- **Účel**: Průvodce vytvořením sezóny a hromadnou inicializací konfigurací pro členy.
- **Hlavní akce**: Inicializovat konfigurace (Start sezóny).
- **FAQ**: "Co se stane s platbami z minulé sezóny?"

### 4. Tréninky a docházka
- **Slug**: `treninky-a-dochazka`
- **Audience**: `admin`, `coach`
- **Typ**: Detailní sekční článek
- **Účel**: Zadávání tréninků pro týmy a vedení prezence. Vysvětlení pojmu "Mismatch".
- **Hlavní akce**: Vytvořit trénink, Zapsat docházku.
- **Related**: `zápasy-a-nominace`.

### 5. Zápasy a nominace hráčů
- **Slug**: `zapasy-a-nominace`
- **Audience**: `admin`, `coach`
- **Typ**: Detailní sekční článek
- **Účel**: Kompletní workflow od vytvoření zápasu přes nominaci hráčů až po zápis výsledku.
- **Hlavní akce**: Odeslat pozvánky hráčům, Zapsat výsledek, Synchronizace zápasů.
- **Related**: `souperi`, `treninky-a-dochazka`.

### 6. Adresář soupeřů
- **Slug**: `souperi`
- **Audience**: `admin`, `coach`
- **Typ**: Detailní sekční článek
- **Účel**: Správa databáze klubů a řešení duplicit při importech.
- **Hlavní akce**: Sloučit duplicitní soupeře (Widget).

### 7. Sportovní údaje hráče
- **Slug**: `sportovni-udaje-hrace`
- **Audience**: `admin`, `coach`, `player` (omezeně)
- **Typ**: Detailní sekční článek
- **Účel**: Popis tabu "Hráč" v profilu. Dresy, licence, zdravotní poznámky a fyzické parametry.
- **Related**: `hracske-profily` (Batch 02).

### 8. Omlouvání z akcí
- **Slug**: `omlouvani-z-akci`
- **Audience**: `player`, `parent`
- **Typ**: Onboarding článek
- **Účel**: Návod pro hráče a rodiče, jak se omluvit z tréninku nebo zápasu v mobilní aplikaci.
- **FAQ**: "Do kdy se mohu omluvit?", "Co znamenají barvy v kalendáři?"

---

## Quick Actions (Návrh)
- `admin.teams.index` -> Přejít na správu týmů
- `admin.trainings.create` -> Naplánovat nový trénink
- `admin.basketball-matches.index` -> Rozpis zápasů
- `admin.seasons.index` -> Nastavení sezón

## FAQ (Návrh)
- **Q**: Co je to "Mismatch" v docházce?
  - **A**: Rozpor mezi tím, co hráč nahlásil (např. potvrdil účast) a co trenér zapsal jako realitu (např. hráč nepřišel).
- **Q**: Jak funguje synchronizace z cz.basketball?
  - **A**: Pokud vyplníte Externí ID týmu, systém automaticky stahuje soupisku a zápasy přímo z oficiálního webu federace.
- **Q**: Mohu přiřadit jednoho trenéra k více týmům?
  - **A**: Ano, v detailu týmu můžete vybrat libovolný počet trenérů.
