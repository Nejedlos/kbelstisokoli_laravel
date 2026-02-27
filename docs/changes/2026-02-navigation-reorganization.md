# Reorganizace veřejné navigace (Únor 2026)

Na základě požadavku na zlepšení UX a SEO byla provedena reorganizace hlavního menu (veřejné navigace).

## Původní pořadí
1. Domů
2. Novinky
3. Galerie
4. Zápasy
5. Tým
6. Nábor
7. Tréninky
8. Historie
9. Kontakt

## Nové (optimalizované) pořadí
1. **Domů** (`nav.home`)
2. **Novinky** (`nav.news`)
3. **Týmy** (`nav.team`)
4. **Zápasy** (`nav.matches`)
5. **Tréninky** (`nav.trainings`)
6. **Nábor** (`nav.recruitment`)
7. **Fotogalerie** (`nav.gallery`)
8. **Historie** (`nav.history`)
9. **Kontakt** (`nav.contact`)

## Zdůvodnění změn

### 1. Uživatelský prožitek (UX)
- **Logická hierarchie:** Uživatelé nejčastěji hledají aktuální zprávy, pak informace o týmech a jejich zápasech.
- **Kontextuální návaznost:** Položky "Tréninky" a "Nábor" jsou nyní u sebe, což dává smysl pro zájemce o vstup do klubu.
- **Odunsuňutí doplňků:** Galerie a historie jsou zajímavé, ale nejsou primárním cílem většiny návštěv, proto byly posunuty dále v menu.

### 2. SEO optimalizace
- **Prioritizace klíčových slov:** Důležitější sekce s relevantním obsahem (Týmy, Zápasy, Tréninky) jsou v DOMu výše, což dává vyhledávačům signál o jejich důležitosti.
- **Nábor:** Sekce náboru je nyní lépe umístěna v rámci flow informací o klubu.

### 3. Technická realizace
Změna byla provedena v souboru `config/navigation.php`, který slouží jako centrální zdroj pravdy pro záhlaví (`x-header`) i zápatí (`x-footer`) webu.
