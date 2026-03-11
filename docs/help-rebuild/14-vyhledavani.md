# Design Vyhledávání v Help Centru

Tento dokument definuje strategii, algoritmus a UX prvky pro vyhledávání v novém help systému založeném na databázi.

## 1. Cíle vyhledávání
- **Rychlost:** Okamžitá odezva při psaní (Livewire).
- **Relevance:** Nejdůležitější výsledky nahoře (vážený ranking).
- **Kontext:** Uživatel vidí, proč byl výsledek nalezen (snippety).
- **Robustnost:** Hledání v názvech, obsahu, klíčových slovech, FAQ i rychlých akcích.

## 2. Datový model a indexace
Vyhledávání probíhá nad modelem `HelpArticle` a jeho relacemi. Protože používáme JSON lokalizaci (`spatie/laravel-translatable`), vyhledáváme v konkrétních jazykových klíčích.

### Prohledávaná pole a váhy (Relevance Score)
| Pole | Váha | Význam |
| :--- | :--- | :--- |
| `title` | 10 | Přesná shoda v nadpisu je nejdůležitější. |
| `search_keywords` | 8 | Manuálně definovaná klíčová slova a synonyma. |
| `metadata->purpose` | 5 | Účel stránky (stručné shrnutí). |
| `content` | 3 | Hlavní text článku (Markdown). |
| `faq` | 2 | Otázky a odpovědi v rámci článku. |
| `quick_actions` | 2 | Názvy a popisky rychlých akcí. |

## 3. Algoritmus Rankingu
Pro SQLite (vývoj) i MySQL (produkce) implementujeme jednoduchý, ale účinný simulovaný ranking pomocí `CASE WHEN` nebo v PHP po načtení (podle výkonu). Preferujeme DB-level ranking pro efektivní limitování výsledků.

```sql
SELECT *, 
  (CASE WHEN title->'$.cs' LIKE '%dotaz%' THEN 10 ELSE 0 END +
   CASE WHEN search_keywords->'$.cs' LIKE '%dotaz%' THEN 8 ELSE 0 END +
   ...) as relevance
FROM help_articles
ORDER BY relevance DESC
```

## 4. Search Snippets (Úryvky)
Pro každý výsledek vyhledávání vygenerujeme krátký úryvek (snippet):
- Pokud se shoda najde v `title`, použijeme `metadata->purpose`.
- Pokud se shoda najde v `content`, vyřízneme cca 160 znaků kolem prvního výskytu hledaného slova.
- Hledaný výraz v úryvku zvýrazníme (`<mark>` nebo `font-bold`).

## 5. UX prvky
### Search Box
- Ikona lupy.
- "Clear" tlačítko pro smazání dotazu.
- Klávesová zkratka `/` pro focus (standardní v knowledge base).

### No Results State
Pokud není nic nalezeno, zobrazíme:
- Informaci "Nebylo nic nalezeno pro: [dotaz]".
- Doporučení:
    - Zkuste obecnější klíčové slovo.
    - Zkontrolujte překlepy.
    - Podívejte se do sekce "Často kladené dotazy".
- Tlačítko pro návrat na úvodní stránku nápovědy.

### Pokročilé funkce (Příprava)
- **Synonyma:** Uložena v `search_keywords`.
- **Role filtering:** Automaticky filtruje výsledky podle `audience_roles` aktuálního uživatele.
- **Section filtering:** Možnost omezit hledání na konkrétní kategorii.

## 6. Implementační detaily
- **Třída:** `App\Services\Help\HelpSearchService`
- **Metoda:** `search(string $query)`
- **Cache:** Vyhledávání nebudeme cachovat (příliš mnoho variací), ale optimalizujeme dotazy.
- **Limit:** Maximálně 20 výsledků pro zachování rychlosti.
