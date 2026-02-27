### Oprava a vylepšení dat zápasů (2026-02-27)

V rámci tohoto úkolu byla vyřešena neúplnost a špatné zobrazení zápasů pro sezóny 2024/2025 a 2025/2026.

#### Hlavní provedené změny:

1.  **Oprava stavů zápasů (Status fix):**
    *   Zápasy v minulosti, které měly stav `scheduled` (plánováno), byly hromadně aktualizovány na `played` (odehráno).
    *   Díky tomu se těchto ~212 zápasů (zejména ze sezóny 2025/2026) začalo správně zobrazovat v sekci "Poslední výsledky" namísto toho, aby byly neviditelné.

2.  **Migrace celoklubových zápasů:**
    *   Byl vytvořen nový systémový tým **"Sokoli (Celý klub)"** (slug: `klub`).
    *   Došlo k re-migraci 38 zápasů ze staré databáze (původní ID týmu 3), které dříve chyběly nebo byly nesprávně přiřazeny.
    *   Tyto zápasy jsou v UI jasně označeny štítkem "CELÝ KLUB" a ikonou.

3.  **Vizuální vylepšení komponenty x-match-card:**
    *   **Barevné rozlišení typů zápasů:** Každý typ (Mistrovské, Pohárové, Turnaj, Přátelské) má nyní svou barvu a ikonu (trofej, medaile atd.).
    *   **Zvýraznění týmu:** Klubový název "Sokoli" je v rozpisu zvýrazněn klubovou oranžovou barvou.
    *   **Responzivita:** Štítky typů zápasů se nyní správně zobrazují i na mobilních zařízeních.

4.  **Optimalizace importu (EventMigrationSeeder):**
    *   Seeder byl upraven tak, aby automaticky nastavoval stav `played` u všech importovaných zápasů, které jsou starší než 2 hodiny od aktuálního času, i když u nich chybí skóre.
    *   Bylo opraveno mapování týmů ze staré databáze.

#### Technické detaily:
- Nový tým: Název `Sokoli (Celý klub)`, slug `klub`, kategorie `all`.
- Barevné schéma typů:
    - MI (Mistrovské): Modrá
    - PO (Pohárové): Fialová
    - TUR (Turnaj): Zelená
    - PRATEL: Šedá
- Příkaz pro opravu stavů: `php artisan matches:update-past-status` (začleněno do seederu).
