# Obsahový standard help stránky

Tento dokument definuje kanonickou strukturu jednoho článku nápovědy pro projekt Kbelští sokoli. Dodržování tohoto standardu zajišťuje konzistenci, vysokou kvalitu a profesionální dojem z celé nápovědy.

## 1. Celková struktura článku

Každý článek se skládá ze tří vrstev:
1.  **Metadata (Technická vrstva)** – definována v poli `$articles` v `HelpArticleSeeder.php`.
2.  **Hlavní tělo (Obsahová vrstva)** – Markdown soubor v `database/seeders/Help/content/`.
3.  **Doplňkové entity (Interaktivní vrstva)** – FAQ a Rychlé akce (Quick Actions).

---

## 2. Metadata (v Seederu)

Tato pole jsou povinná nebo doporučená pro správné fungování UI a navigace.

### Povinná pole
- `slug`: Unikátní identifikátor (např. `sprava-dochazky`).
- `title`: Název článku (lokalizovaný).
- `sort_order`: Pořadí v rámci kategorie (desítková řada: 10, 20, 30...).
- `is_active`: `true` pro zobrazení.
- `audience_roles`: Pole rolí (např. `['coach', 'admin']`), které článek uvidí.

### Doporučená a volitelná pole
- `is_featured`: `true` pro zobrazení na domovské stránce helpu.
- `short_intro`: 1-2 věty shrnující přínos článku (zobrazuje se v seznamech).
- `search_keywords`: Pole klíčových slov a synonym (např. `['omluvenky', 'absence']`).
- `metadata.purpose`: Textový popis účelu dané sekce systému (zobrazuje se v detailu článku).
- `metadata.audience_summary`: Stručné shrnutí, pro koho je tento návod určen (např. "Primárně pro trenéry a vedoucí týmů").

### Příklad definice v seederu
```php
[
    'category_slug' => 'sport',
    'data' => [
        'slug' => 'sprava-dochazky',
        'sort_order' => 10,
        'is_active' => true,
        'audience_roles' => ['coach', 'admin'],
        'metadata' => [
            'purpose' => 'Evidence účasti členů na sportovních aktivitách klubu.',
            'audience_summary' => 'Primárně pro trenéry a vedoucí týmů.',
        ],
    ],
    'translations' => [
        'title' => ['cs' => 'Správa docházky', 'en' => 'Attendance Management'],
        'short_intro' => ['cs' => 'Návod pro trenéry...', 'en' => 'Guide for coaches...'],
    ],
    'faqs' => [...],
    'quick_actions' => [...],
]
```

---

## 3. Struktura Markdownu (v souboru)

> [!IMPORTANT]
> **Markdown soubor NESMÍ obsahovat nadpis první úrovně (H1 / #).**
> Název článku se automaticky bere z databáze a vykresluje se v šabloně stránky. Vložení H1 v Markdownu způsobí duplicitu nadpisu v UI.

Obsah musí být logicky členěn pomocí H2 a H3.

### Povinné sekce (H2)
1.  **## Přehled obrazovky**
    - Stručný popis toho, co uživatel vidí po vstupu do dané sekce v administraci.
    - Identifikace hlavních prvků (tabulka, filtry, postranní panel).
2.  **## Postup práce** (nebo "Jak na to")
    - Číslovaný seznam kroků pro nejčastější úkol (např. "Jak zapsat docházku").
    - Musí být jasné, na co kliknout a co je výsledkem.
3.  **## Akce a tlačítka**
    - Popis specifických tlačítek (např. "Exportovat do PDF", "Hromadné omluvení").

### Doporučené sekce (H2)
- **## Popis polí** – pokud formulář obsahuje mnoho nebo nejasná pole.
- **## Filtrování a vyhledávání** – jak efektivně najít data.
- **## Časté chyby (Common Mistakes)** – blok upozorňující na to, co nedělat.
- **## Tipy a doporučení (Best Practices)** – jak si usnadnit práci.

### Formátování v Markdownu
- **Tučné písmo**: Pro názvy prvků v UI (např. Klikněte na tlačítko **Uložit**).
- **Kurzíva**: Pro volitelné kroky nebo doplňující info.
- **Callouty**:
    - **Varování**: Používejte citaci uvozenou slovem "POZOR:" nebo "VAROVÁNÍ:".
    - **Tip**: Používejte citaci uvozenou slovem "TIP:".

---

## 4. Doplňkové entity

### FAQ (Časté dotazy)
- Každý článek by měl mít 1-3 specifické dotazy.
- Dotazy musí být praktické, vycházející z reálných problémů uživatelů.

### Rychlé akce (Quick Actions)
- Minimálně 1 akce vedoucí přímo do dané sekce v administraci.
- Label musí být akční (např. "Přejít na správu docházky").

---

## 5. Golden Sample (Referenční standard)

Pro ověření kvality nového článku použijte "Golden Sample" (článek `sprava-dochazky`), který obsahuje všechny výše zmíněné prvky v maximální kvalitě.
