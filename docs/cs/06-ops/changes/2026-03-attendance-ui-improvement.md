# Vylepšení UI docházky v členské sekci (01. 03. 2026)

## Popis změn
Cílem bylo zpřehlednit detail události (trénink, zápas) v členské sekci tak, aby bylo na první pohled jasné, kdo se akce účastní a kdo nikoliv.

### Seznam úprav:
- **Vizualizace účastníků:**
    - Místo prostých iniciál se nyní zobrazují avatary uživatelů (pokud jsou k dispozici).
    - Každý avatar má v rohu barevný indikátor statusu (zelená fajfka pro potvrzené, červený křížek pro omluvené atd.).
    - Aktuální uživatel je v seznamu zvýrazněn modrým okrajem a štítkem "(Já)".
    - Položky v seznamu mají nyní jemné pastelové podbarvení odpovídající statusu pro lepší vizuální orientaci.
- **Statistiky:**
    - Karty se statistikami v horní části postranního panelu byly designově vylepšeny o jemné barevné tóny (success, danger, warning) a lepší typografii.
    - Přidán celkový počet pozvaných osob do hlavičky seznamu.
- **Docházka Formulář:**
    - Možnosti potvrzení/omluvy byly doplněny o jemné podbarvení při najetí myší (hover) a po výběru, což usnadňuje interakci.
- **Seznam omluvených:**
    - Nyní se přímo u jména zobrazuje i vybraný důvod omluvy (např. "Nemoc", "Práce"), pokud byl zadán.
- **Interaktivita:**
    - Přidány hover efekty na karty účastníků pro lepší zpětnou vazbu.
    - Seznamy jsou vizuálně odděleny barevnými linkami odpovídajícími statusu (zelená, červená, žlutá).
- **Responzivita:**
    - Vylepšeno odsazení a velikosti prvků pro mobilní zařízení.

## Technické detaily
- Upraven soubor: `resources/views/member/attendance/show.blade.php`
- Využití `User::getAvatarUrl('thumb')` pro načítání avatarů.
- Použití standardních Font Awesome 7 Light/Solid ikon dle projektu.
- Využití Tailwind utility tříd pro design.
