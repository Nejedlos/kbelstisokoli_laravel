# Sportovní modul a Docházka

Účel: Správa sportovních dat oddílu (týmy, zápasy, tréninky, sezóny) a univerzální systém pro potvrzování účasti (Docházka).

## 1. Sportovní modul (Core)

### Datový model (ER)
- **Seasons (Sezóny):** Název (např. 2024/25), aktivní stav.
- **Teams (Týmy):** Kategorie (např. U11, Muži), popis, slug.
- **Opponents (Soupeři):** Název, město, logo.
- **BasketballMatches (Zápasy):** Vazba na tým, sezónu a soupeře. Pole: datum, místo, skóre, stav (plánováno, odehráno...), domácí/hosté.
- **Trainings (Tréninky):** Jednotlivé termíny tréninků pro týmy.
- **Events (Akce):** Klubové akce mimo běžný režim (soustředění, schůze). Podporují nahrávání plakátů a příloh (dokumenty, PDF).

### Vztahy
- `BasketballMatch` patří pod `Team`, `Season` a `Opponent`.
- Formulář zápasu vyhledává soupeře podle celého zadaného názvu. Pokud normalizovaný název (bez nadbytečných mezer a bez rozlišení velikosti písmen) přesně neexistuje, nabídne syntetickou volbu pro okamžité vytvoření záznamu `Opponent` pouze s názvem. Po výběru ji server převede na číselné `opponent_id`; syntetická hodnota se do zápasu neukládá. Stejný tok používá vytvoření i editace zápasu a při každém zápisu znovu ověřuje oprávnění `manage_matches`.
- `Training` patří pod `Team`.
- `Team` má mnoho `BasketballMatch` a `Training`.
- `Team` rozlišuje mezi všemi hráči v týmu a hráči na **oficiální soupisce** (`is_on_roster` v pivotu).
- `User` může mít více `PlayerProfile` záznamů (historie působení).
- `Attendance` (Docházka) má polymorfní vazbu na `Training`, `BasketballMatch` a `ClubEvent`.

## 2. Hráčské profily a historie (Stinty)
Účel: Sledování vývoje hráče v čase (změny týmu, čísla dresu, pozice).

### Datový model
- **PlayerProfile:** Obsahuje sportovní data (číslo dresu, pozice, výška, váha).
- **Časová platnost:** Pole `valid_from` a `valid_to` určují, kdy byl profil aktivní.
- **Vazba:** Uživatel má vždy jeden aktuálně aktivní profil (alias `playerProfile` v modelu User), ale v administraci je dostupná kompletní historie.

### Transfery (Změna působení)
Při změně týmu nebo čísla dresu se doporučuje použít akci **Transfer** v administraci uživatele:
1. Původní profil se ukončí k zadanému datu (`valid_to`).
2. Vytvoří se nový profil s novými údaji, platný od daného data.
3. Tím je zachována integrita historických statistik a soupisek.

## 3. Docházka a Docházka (Docházkový systém)
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
   - V administraci u konkrétní události vidí tabulku `Docházka / Docházka`.
   - Má okamžitý přehled o počtech potvrzených hráčů.
   - Může doplňovat interní poznámky (např. "Hráč se omluvil telefonicky").
   - Má právo editovat nebo mazat záznamy všech členů.

### Jak přidat nový typ Docházka události
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

## Reálná docházka – hromadný zápis skutečné účasti

V administraci **Reálná docházka** zobrazuje seznam s checkboxy pouze uživatele, kteří potvrdili účast (`planned_status = confirmed`), a již zapsané přítomné (`actual_status = attended`), včetně ručně přidaných hráčů. Potvrzení jsou první a každý hráč se zobrazí jen jednou. Ostatní členové týmu ani odpovědi „nepřijdu“, „možná“ a „bez odpovědi“ se automaticky nezobrazují; hráče lze vyhledat a přidat. Odškrtnutý ručně přidaný hráč bez příslibu ze seznamu zmizí a zůstane dostupný ve vyhledávání. Potvrzený hráč zůstává v seznamu i po odškrtnutí.

Checkboxy ukládají ihned pouze `actual_status`: zaškrtnutí `attended`, odškrtnutí `null`. Původní odpověď, omluva, poznámky a `responded_at` zůstávají zachované. Tlačítko **Označit potvrzené jako přítomné** hromadně zapíše pouze skutečnou přítomnost aktuálně potvrzených hráčů; ostatní záznamy nemění. Vyhledávání od dvou znaků umožňuje přidat uživatele s aktivním hráčským profilem i z jiného týmu. Přidání rovnou zapíše skutečnou přítomnost.

Zápis checkboxy negeneruje platby. Samostatná potvrzovaná finalizace používá dosavadní finanční službu a doplní absence podle jejích pravidel. Zrušení přítomnosti není automatická omluva ani zpětné zrušení již vystavených plateb.

`RealAttendanceService` sestavuje seznam a provádí změny skutečné účasti. Livewire tracker kontroluje `manage_attendance` nebo roli `super_admin` při každém požadavku, zamyká identitu události a dovoluje pouze `Training`, `BasketballMatch`, `ClubEvent`. Odebrání docházky je omezené na právě otevřenou událost.
