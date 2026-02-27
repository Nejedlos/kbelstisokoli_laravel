### Oprava a vylepšení dat zápasů (2026-02-27)

V rámci tohoto úkolu byla vyřešena neúplnost dat, špatné zobrazení zápasů a implementována podpora pro přiřazení více týmů k jedné události.

#### Hlavní provedené změny:

1.  **M:N vztah pro zápasy a týmy:**
    *   Zápasy byly převedeny z pevné vazby 1:N na **M:N (Many-to-Many)** pomocí nové pivot tabulky `basketball_match_team`.
    *   To umožňuje přiřadit k jednomu zápasu více týmů (např. C i E zároveň).
    *   V administraci (Filament) byl upraven formulář zápasu na `multiselect` pro týmy.

2.  **Migrace celoklubových dat:**
    *   U všech událostí (zápasy, tréninky, akce), které byly ve staré DB označeny jako celoklubové (ID 3), jsou nyní automaticky přiřazeny týmy **Muži C**, **Muži E** i systémový tým **Sokoli (Celý klub)**.
    *   Tím je zajištěno, že se tyto akce zobrazí ve filtrech pro oba hlavní týmy.

3.  **Vizuální vylepšení a UI:**
    *   **Zobrazení týmů:** Pokud má zápas více týmů nebo je označen jako celoklubový, v komponentě `x-match-card` se zobrazí výrazný štítek **"CELÝ KLUB"** nebo výčet týmů (např. "Muži C & Muži E").
    *   **Barevné rozlišení typů zápasů:** Mistrovské (modrá), Pohárové (fialová), Turnaj (zelená), Přátelské (šedá).
    *   **Zvýraznění v detailu:** Stránka detailu zápasu nyní také korektně zobrazuje názvy všech přiřazených týmů v nadpisu i meta informacích.
    *   **Oprava zobrazení výsledků:** Vyřešen problém, kdy se u zápasů se stavem `played` (automaticky nastaveno u historických dat) nezobrazovalo skóre v seznamu i v detailu zápasu.
    *   **Robustnější kontrola skóre:** V komponentě `x-match-card` i v detailu `show.blade.php` byla upravena podmínka pro zobrazení výsledků tak, aby správně pracovala s hodnotou `0` (pomocí `isset()` / `!is_null()`) a lépe detekovala vyplněné výsledky i pro stav `played`.
    *   **Podpora stavů v administraci:** Filament administrace nyní plně podporuje stavy `played` (odehráno ze svazu) a `scheduled` (naplánováno ze svazu), včetně možnosti editace skóre u těchto stavů.

4.  **Oprava stavů a historických dat:**
    *   Historické zápasy (přes 200 záznamů), které byly dříve neviditelné kvůli stavu `scheduled`, byly opraveny na `played`.
    *   **Oprava prohazování skóre u zápasů venku:** V seederu byla opravena kritická chyba, kdy se skóre `naše:soupeř` ze staré DB ukládalo jako `home:away` bez ohledu na místo konání. Nyní se u zápasů venku skóre prohodí, aby odpovídalo skutečnému stavu (domácí soupeř : hostující Sokoli).
    *   **Diagnostika chybějících výsledků pro 2025/2026:** Potvrzeno, že v tabulce `zapasy` (stará DB) jsou u aktuální sezóny výsledky skutečně `NULL`. Data je nutné doplnit ručně nebo přes externí import.
    *   Seeder nyní automaticky detekuje stav na základě času konání.

#### Technické detaily:
- Nová pivot tabulka: `basketball_match_team`.
- Aktualizované modely: `BasketballMatch`, `Team`, `Training`, `ClubEvent`.
- Aktualizované Filament resources: `BasketballMatchResource`, `TrainingResource`, `ClubEventResource`.
- Seeder: `EventMigrationSeeder` nyní používá `sync()` pro všechny typy událostí.
