# Import ze starého Markdown systému

Tento dokument popisuje proces a logiku jednorázového importu nápovědy ze starého souborového systému (`docs/help/**`) do nového databázového řešení.

## 1. Účel importu
Cílem je zachovat stávající obsah nápovědy, který byl vytvořen během dřívějších fází projektu, a transformovat jej do strukturovaného formátu v databázi. Tím získáme "startovní čáru" pro nový help systém bez nutnosti vše přepisovat ručně.

## 2. Zdrojová struktura
Zdrojová data se nacházejí v:
- `docs/help/cs/` (Čeština)
- `docs/help/en/` (Angličtina)

Struktura odpovídá:
- `{priority}-{category-slug}/` -> Kategorie
- `{priority}-{category-slug}/README.md` -> Metadata kategorie (název, popis)
- `{priority}-{category-slug}/{priority}-{article-slug}.md` -> Článek nápovědy

## 3. Logika mapování

### Kategorie (`HelpCategory`)
- **Název (Name):** Získán z H1 v `README.md` nebo z překladového klíče `admin.help.categories.{slug}.name`.
- **Slug:** Odvozen z názvu složky (bez číselného prefixu).
- **Popis (Description):** První odstavec v `README.md` (pod H1) nebo z překladového klíče.
- **Pořadí (Sort Order):** Extrahováno z prefixu složky (např. `01-` -> 1).
- **Ikona a Barva:** Mapováno podle klíčových slov v názvu složky (sport, lide, finance, obsah, system).

### Články (`HelpArticle`)
- **Název (Title):** Extrahován z prvního H1 v `.md` souboru.
- **Slug:** Odvozen z názvu souboru (bez číselného prefixu a přípony).
- **Obsah (Content):** Celý text souboru (po odstranění úvodního H1).
- **Metadata:**
    - **Short Intro:** Extrahováno z prvního odstavce pod nadpisem.
    - **Audience:** Pokus o detekci ze sekce "Pro koho je sekce určena".
- **Pořadí (Sort Order):** Extrahováno z prefixu souboru.
- **Vazby:** Pokus o rozpoznání "Souvisejících sekcí" na konci souboru.

## 4. Implementace
Import je realizován pomocí Artisan příkazu:
```bash
php artisan help:import-legacy
```

Příkaz provádí:
1. Smazání (volitelně) nebo aktualizaci stávajících dat.
2. Iteraci přes složky češtiny (primární jazyk).
3. Vytvoření kategorií.
4. Vytvoření článků pro každou kategorii.
5. Doplnění anglických překladů, pokud existují odpovídající soubory v `docs/help/en/`.
6. Výpočet MD5 hashů pro budoucí synchronizaci.

## 5. Bezpečnost a opakovatelnost
- Importér nastavuje příznak `is_customized = false`, což signalizuje, že obsah je "systémový".
- Při opakovaném spuštění aktualizuje záznamy, které nebyly ručně upraveny v administraci.
- Původní soubory v `docs/help/` zůstávají nedotčeny.

## 6. Omezení
- Komplexní bloky (např. FAQ vnořené v textu) se importují jako součást hlavního textu (Markdown).
- Rychlé akce (Quick Actions) nejsou automaticky extrahovány, musí být doplněny ručně přes admin.
- Odkazy mezi články mohou vyžadovat manuální korekci slugů.
