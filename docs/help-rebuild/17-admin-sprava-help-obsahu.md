# Interní správa help obsahu (Filament)

Tento dokument popisuje implementaci administrátorského rozhraní pro správu nápovědy v projektu Kbelští sokoli.

## 1. Architektura rozhraní
Správa nápovědy je realizována pomocí dvou hlavních Filament Resources umístěných v navigaci pod skupinou **Systém**:

1. **Kategorie nápovědy (`HelpCategoryResource`):**
    - Slouží k definici hierarchické struktury (sekce, podsekce).
    - Umožňuje nastavení ikon (Font Awesome Light), barev a cílových rolí pro celou sekci.
    - Podporuje bilingvní názvy a popisy.

2. **Články nápovědy (`HelpArticleResource`):**
    - Hlavní správa obsahu.
    - Obsahuje Markdown editor pro tělo článku (bilingvně).
    - Integruje FAQ a Rychlé akce přes Relation Managery.
    - Umožňuje propojení se souvisejícími články.

## 2. Klíčové funkce administrace

### Lokalizace (Bilingvnost)
V souladu s projekčními guidelines nepoužíváme dedikovaný plugin pro překlady. Místo toho využíváme "dot notation" přímo ve formulářích (např. `title.cs`, `title.en`).
- Veškeré textové vstupy (nadpisy, obsahy, popisy) jsou k dispozici v obou jazycích.
- Tabulky v administraci zobrazují primárně českou verzi, ale umožňují vyhledávání v obou jazycích.

### Správa rolí (Audience-aware)
U kategorií i článků lze v poli **Cílové role** vybrat jednu nebo více rolí (Trenér, Hráč, Rodič atd.).
- Obsah označený těmito rolemi je v uživatelském rozhraní prioritizován a označen příslušným badge.
- Pokud není vybrána žádná role, obsah je považován za obecný (viditelný pro všechny).

### Ochrana seedovaného obsahu
Většina nápovědy je primárně spravována přes zdrojové soubory (Markdown) v Gitu a synchronizována pomocí seederů.
- **`is_customized`:** Každý záznam má tento příznak.
    - `false` (výchozí pro seedy): Obsah je při každém spuštění seederu aktualizován podle souborů. V adminu se zobrazuje varovný panel, že jde o systémový obsah.
    - `true`: Pokud administrátor v adminu článek ručně upraví a uloží, příznak se automaticky přepne na `true` (nebo jej lze přepnout manuálně). Takový obsah je seederem ignorován a zůstává zachován.

## 3. Technické detaily

### Použité komponenty
- **Markdown Editor:** Pro bilingvní psaní obsahu článků.
- **Tabs:** Rozdělení formuláře článku na *Obsah*, *Nastavení* a *Metadata* pro lepší přehlednost.
- **Relation Managers:**
    - `FaqsRelationManager`: Správa otázek a odpovědí přímo u článku.
    - `QuickActionsRelationManager`: Definice odkazů na akce v systému (podporuje FA ikony a routy).
    - `RelatedArticlesRelationManager`: M:N vazba pro doporučení dalších témat.

### Ikony
V souladu s guidelines nepoužíváme standardní Heroicons pro navigaci, ale **Font Awesome 7 Pro Light**.
- Resource ikony jsou definovány přes `IconHelper::get(AppIcon::...)`.
- Ikony v tabulkách a rychlých akcích jsou renderovány jako `HtmlString` s příslušnými FA třídami.

## 4. Budoucí rozšiřitelnost
- Systém je připraven na přidání dalších typů metadat do JSON pole `metadata`.
- Vyhledávání v administraci je optimalizováno pro JSON sloupce (podpora SQLite i MySQL).
