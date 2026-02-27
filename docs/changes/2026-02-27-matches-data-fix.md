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

4.  **Oprava stavů a historických dat:**
    *   Historické zápasy (přes 200 záznamů), které byly dříve neviditelné kvůli stavu `scheduled`, byly opraveny na `played`.
    *   Seeder nyní automaticky detekuje stav na základě času konání.

#### Technické detaily:
- Nová pivot tabulka: `basketball_match_team`.
- Aktualizované modely: `BasketballMatch`, `Team`, `Training`, `ClubEvent`.
- Aktualizované Filament resources: `BasketballMatchResource`, `TrainingResource`, `ClubEventResource`.
- Seeder: `EventMigrationSeeder` nyní používá `sync()` pro všechny typy událostí.
