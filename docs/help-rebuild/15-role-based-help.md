# Role-based chování help systému

Tento dokument popisuje implementaci a logiku chování nápovědy na základě uživatelských rolí v projektu Kbelští sokoli.

## 1. Cíle role-based systému
- **Personalizace:** Uživatel vidí primárně to, co je pro něj v jeho roli (trenér, hráč, rodič) důležité.
- **Redukce šumu:** Skrytí technických nebo administrativních návodů pro běžné členy.
- **Plná viditelnost pro správce:** Administrátoři mají přístup k veškerému obsahu bez omezení.
- **Prioritizace:** Relevantní obsah je v seznamech řazen výše.

## 2. Definice rolí (Audience)
V systému pracujeme s následujícími rolemi (odpovídají Spatie Permission rolím):
- `admin` - Administrátor systému (vidí vše).
- `coach` - Trenér (vidí sportovní agendu, docházku, zápasy).
- `editor` - Redaktor/Správce obsahu (vidí správu webu a článků).
- `player` - Hráč (vidí členskou sekci, své statistiky).
- `parent` - Rodič (vidí členskou sekci, platby dětí).

## 3. Technická implementace

### Datový model
V tabulkách `help_categories` a `help_articles` je sloupec `audience_roles` typu JSON.
- Pokud je `null`, je obsah veřejný pro všechny přihlášené uživatele.
- Pokud obsahuje pole rolí, je viditelný pouze pro uživatele s těmito rolemi (nebo pro `admin`).

### Filtrování (Query Layer)
V `HelpQueryService` je implementována logika:
1. Získání rolí aktuálního uživatele.
2. Pokud uživatel má roli `admin`, filtry se deaktivují (`getFilteringRoles()` vrací prázdné pole).
3. Pro ostatní se aplikuje scope `forAudience($roles)`, který provádí `JSON_CONTAINS` (nebo ekvivalent v SQLite).

### Ranking a řazení
Obsah není jen filtrován, ale i dynamicky řazen v rámci kolekcí v `HelpQueryService`:
- **Váha 1000:** Shoda s uživatelskou rolí (položka je určena přímo pro něj).
- **Váha 100:** Položka je označena jako `is_featured`.
- **Základní řazení:** Dle `sort_order` definovaného v administraci/seederu.

Tímto je zajištěno, že trenér uvidí "Správu docházky" na prvním místě, i když je v kategorii s deseti dalšími články.

## 4. Uživatelské rozhraní (UX)

### Vizuální indikátory
Komponenta `article-card` využívá data o rolích k odlišení:
- **Odznak "Pro vaši roli":** Zobrazuje se u článků, které mají shodu s rolí uživatele. Tyto karty mají navíc jemné podbarvení brandovou barvou (`primary-50`).
- **Odznak "Doporučeno":** Zobrazuje se u globálně důležitých článků (`is_featured`), které nejsou specifické pro roli uživatele.

### Home Page
Landing page nápovědy prioritizuje doporučené návody tak, aby ty nejrelevantnější pro aktuálního uživatele byly vždy na prvních pozicích.

## 5. Správa obsahu (Seeding)
Při tvorbě seederů je nutné definovat `audience_roles` v poli `data`:

```php
'data' => [
    'slug' => 'sprava-dochazky',
    'audience_roles' => ['coach', 'admin'],
    'is_featured' => true,
    // ...
]
```

## 6. Budoucí rozšiřitelnost
Systém je připraven na:
- **Důrazné varování:** Možnost přidat roli "new_user" pro onboarding.
- **Role-based Quick Actions:** Aktuálně jsou akce vázány na článek, ale lze je v budoucnu filtrovat i samostatně.
- **Analytika:** Sledování, zda uživatelé skutečně klikají na "Pro vaši roli" články častěji.
