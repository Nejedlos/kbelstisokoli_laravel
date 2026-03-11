# Správa a rozšiřování nápovědy

Tento dokument slouží pro administrátory a vývojáře jako návod, jak udržovat a rozšiřovat systém nápovědy.

## Struktura souborů
Nápověda je uložena v adresáři `docs/help/cs/` ve formátu Markdown.
- Hlavní sekce (např. Sportovní agenda) mají svůj adresář (např. `02-sportovni-agenda/`).
- Každý adresář sekce obsahuje soubor `README.md` se stručným úvodem.
- Jednotlivé stránky nápovědy jsou soubory `.md` (např. `01-tymy.md`).
- Číslování v názvech souborů a adresářů zajišťuje správné řazení v levém menu nápovědy.

## Jak přidat novou stránku nápovědy
1. **Zvolte správné umístění:** Pokud se jedná o novou funkci v existující sekci, přidejte soubor do jejího adresáře.
2. **Dodržte šablonu:** Každá stránka by měla obsahovat:
    - Hlavní nadpis (# Název)
    - Breadcrumbs (Nápověda > Sekce > Stránka)
    - Cílové role (Pro koho je sekce určena)
    - Vysvětlení sekce (K čemu slouží)
    - Nejčastější akce (Krokové návody)
    - Popis obrazovky a polí
    - Časté chyby a upozornění
    - Související sekce
3. **Jazykové verze:** Pokud je systém v angličtině, vytvořte kopii v `docs/help/en/`.
4. **Odkazy:** Pokud odkazujete na jinou stránku nápovědy, používejte relativní cesty (např. `[Uživatelé](../03-lide-a-clenove/01-uzivatele.md)`).

## Strategie psaní textů
- **Pro kouče a editory:** Pište lidsky, srozumitelně, bez technického žargonu. Zaměřte se na to, *co mají kliknout* a *proč*.
- **Pro administrátory:** Můžete být techničtější, vysvětlujte dopady na systém, vazby mezi daty a bezpečnostní rizika.
- **Důraz na "Proč":** Nevysvětlujte jen, že tlačítko "Uložit" uloží data, ale k čemu je uložení v daném kontextu dobré a co se stane potom (např. propis na web).

## Pravidelná údržba
- **Aktualizace screenů/polí:** Při každé změně v UI nebo přidání nového pole do formuláře aktualizujte i odpovídající nápovědu.
- **Kontrola FAQ:** Pokud se uživatelé často ptají na stejnou věc, přidejte ji do sekce FAQ na příslušné stránce.
- **Onboarding:** Nápověda by měla sloužit jako hlavní materiál pro zaučení nových trenérů nebo editorů.
