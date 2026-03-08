### Redesign členské nástěnky (Member Dashboard)

Tento dokument popisuje novou podobu nástěnky v členské sekci a napojení na data.

#### Cíle
- Moderní a přehledný přehled po přihlášení.
- Klíčové informace na jednom místě: profil/zařazení, týmy, aktivita, ekonomika.
- Akce „na kliknutí“ bez inline formulářů (vede na příslušné stránky profilu, programu a plateb).

#### Hlavní bloky UI
1. Profilová karta (nahoře)
   - Avatar (z Media Library, kolekce `avatar`), fallback iniciála.
   - Jméno, role (badges), status členství (badge).
   - Rychlé akce: Upravit profil, Můj program, Platby.
   - Informace: Variabilní symbol (VS), Členské ID, Týmy (prázdný stav je lokalizovaný).

2. KPI přehled
   - Čeká na potvrzení (z nadcházejícího programu dle uživatele).
   - Moje týmy (počet týmů).
   - Moje platby (částka k úhradě – z ekonomického souhrnu). 

3. Program
   - „Nejbližší program“ (3 nejbližší akce – tréninky, zápasy, akce klubu) s CTA na celý přehled.

4. Aktivita
   - Poslední notifikace (max 5), prázdný stav a CTA „Zobrazit vše“ na `member.notifications.index`.

5. Ekonomika – shrnutí
   - Přehled „K úhradě“ a „Po splatnosti“ s CTA na `member.economy.index`.

#### Data a kontroler
- Soubor: `app/Http/Controllers/Member/DashboardController.php`
- Nově doplněno:
  - `FinanceService::getMemberSummary($user)` → `economySummary` (k úhradě, po splatnosti, uhrazeno…)
  - Poslední notifikace: `$user->notifications()->latest()->limit(5)->get()` → `notifications`
  - Avatar URL z Media Library: `$user->getFirstMediaUrl('avatar')` → `avatarUrl`

#### Blade šablona
- Soubor: `resources/views/member/dashboard.blade.php`
- Přepracována struktura na bloky výše, zachována vizuální identita (barvy, rounded, utility) a FA Light ikony.

#### Lokalizace
- Soubory: `lang/cs/member.php`, `lang/en/member.php`
- Přidána nová sekce `dashboard` (profile, actions, activity, economy) pro dvojjazyčné popisky.

#### Navigace/akce
- Upravit profil → `route('member.profile.edit')`
- Můj program → `route('member.attendance.index')`
- Platby → `route('member.economy.index')`
- Oznámení (vše) → `route('member.notifications.index')`

#### Poznámky k rozšíření
- Avatar: aktuálně se mění na stránce profilu (plán: přidat dedikovanou sekci/anchor v profilu).
- Trenérské nástroje: sekce zůstává, data se mohou napojit na skutečná trenérská oprávnění/týmy.

#### Test/ověření
- Rychlá kontrola syntaxe: `php -l app/Http/Controllers/Member/DashboardController.php` OK.
- Po nasazení otestovat přihlášením člena se/bez týmů a s/bez otevřených plateb.
