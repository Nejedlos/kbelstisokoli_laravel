# Sportovní modul a RSVP

Účel: Správa sportovních dat oddílu (týmy, zápasy, tréninky, sezóny) a univerzální systém pro potvrzování účasti (RSVP).

## 1. Sportovní modul (Core)

### Datový model (ER)
- **Seasons (Sezóny):** Název (např. 2024/25), aktivní stav.
- **Teams (Týmy):** Kategorie (např. U11, Muži), popis, slug.
- **Opponents (Soupeři):** Název, město, logo.
- **BasketballMatches (Zápasy):** Vazba na tým, sezónu a soupeře. Pole: datum, místo, skóre, stav (plánováno, odehráno...), domácí/hosté.
- **Trainings (Tréninky):** Jednotlivé termíny tréninků pro týmy.
- **Events (Akce):** Klubové akce mimo běžný režim (soustředění, schůze).

### Vztahy
- `BasketballMatch` patří pod `Team`, `Season` a `Opponent`.
- `Training` patří pod `Team`.
- `Team` má mnoho `BasketballMatch` a `Training`.
- `Attendance` (Docházka) má polymorfní vazbu na `Training`, `BasketballMatch` a `ClubEvent`.

## 2. Docházka a RSVP (RSVP Modul)
Účel: Univerzální systém pro potvrzování účasti a evidenci docházky na všech typech klubových akcí.

### Datový návrh
- **Attendance Model:** Obsahuje `user_id`, `status` (pending, confirmed, declined, maybe), `note`, `internal_note` a polymorfní vazbu `attendable`.
- **Zvolené řešení:** Polymorfní vazba byla zvolena pro maximální rozšiřitelnost. Libovolný nový model (např. `Camp`, `Workshop`) lze do systému zapojit pouhým přidáním vztahu `morphMany`.

### Workflow
1. **Hráč (Člen):**
   - V členské sekci (`/clenska-sekce/program`) vidí chronologický přehled všech nadcházejících událostí.
   - Pomocí rychlých akcí (mobile-first) potvrdí nebo omluví svou účast.
   - U omluvenky může uvést důvod (uloží se do pole `note`).
2. **Trenér / Admin:**
   - V administraci u konkrétní události vidí tabulku `Docházka / RSVP`.
   - Má okamžitý přehled o počtech potvrzených hráčů.
   - Může doplňovat interní poznámky (např. "Hráč se omluvil telefonicky").
   - Má právo editovat nebo mazat záznamy všech členů.

### Jak přidat nový typ RSVP události
1. V modelu nové události přidejte vztah:
   ```php
   public function attendances(): \Illuminate\Database\Eloquent\Relations\MorphMany {
       return $this->morphMany(Attendance::class, 'attendable');
   }
   ```
2. Do Filament Resource přidejte `AttendancesRelationManager`.
3. V `AttendanceController` rozšiřte metodu `index` a `store` o nový typ.

## 3. Veřejné zobrazení
- `/zapasy`: Seznam všech zápasů s paginací.
- `/zapasy/{id}`: Detail zápasu s výsledkem a veřejnou poznámkou.
- `/treninky`: Přehled tréninkových informací rozdělený podle týmů.
