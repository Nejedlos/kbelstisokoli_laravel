# Finální obsah: Batch 02 – Lidé a členové

Tento dokument shrnuje vytvořený help obsah pro druhou dávku (Batch 02), která pokrývá správu uživatelů, hráčů, zájemců a oprávnění.

## Seznam vytvořených článků
V rámci této dávky bylo vytvořeno **6 článků**, každý ve dvou jazykových mutacích (CS, EN) s napojením na reálné UI a workflow:

1.  **Evidence uživatelů a členů** (`evidence-uzivatelu`)
    - Zaměřeno na práci s tabulkou, vyhledávání, filtry a hromadné akce.
    - Obsahuje vysvětlení "Ghost" profilů a duplicit.
2.  **Správa a editace člena** (`sprava-clena`)
    - Detailní rozbor 6 záložek ve formuláři `UserResource`.
    - Popis přiřazování k týmům přes sezónní konfigurace.
3.  **Rodinné vazby (Rodič a dítě)** (`rodinne-vazby`)
    - Návod na propojení účtů a výhody pro členskou sekci (přepínání profilů).
4.  **Hráčské profily a historie** (`hracske-profily`)
    - Vysvětlení pojmu "Stint" a správa čísel dresů.
5.  **Nábory a správa zájemců** (`nabory-a-zajemci`)
    - Workflow pro zpracování leadů z webového formuláře.
6.  **Role a oprávnění** (`role-a-opravneni`)
    - Přehled RBAC modelu a návod na přidělování rolí v administraci.

## Technické podklady a verifikace
- **Zdrojová data:** Obsah vychází z UI auditu (`01-ui-audit.md`) a mapy akcí (`03-mapa-castych-akci.md`).
- **Lokalizace:** Všechny články jsou plně bilingvní (CS/EN).
- **Vazby:** Každý článek má definované `audience_roles`, `search_keywords`, FAQ a Quick Actions.
- **Seeder:** Data jsou integrována do `HelpArticleSeeder` a načítána z Markdown souborů v `database/seeders/Help/content/`.

## Kontrola reality (100% potvrzeno)
- **Rodič-Dítě:** Propojení přes `ParentsRelationManager` v detailu dítěte.
- **Stinty:** Historické sledování čísel dresů v `PlayerProfileResource`.
- **Leady:** Manuální převod na člena (potvrzeno, že automatický převod neexistuje).
- **Filtry:** Existující filtry na Role a Týmy v tabulce uživatelů.

## Seznam vytvořených souborů
### Markdown obsah (database/seeders/Help/content/)
- **CS:** `lide/evidence-uzivatelu.md`, `lide/sprava-clena.md`, `lide/rodinne-vazby.md`, `lide/hracske-profily.md`, `lide/nabory-a-zajemci.md`, `lide/role-a-opravneni.md`.
- **EN:** Stejná struktura v adresáři `/en/`.

### Seeding a Dokumentace
- `database/seeders/Help/HelpArticleSeeder.php` (aktualizace datové struktury)
- `docs/help-rebuild/content-batches/02-lide-a-clenove/04-finalni-obsah.md` (tento dokument)
