# Rafinace logiky docházky a RSVP

Datum: 2026-02-26
Autor: Junie

## Účel
Zpřesnění výpočtu statistik docházky a vylepšení uživatelského rozhraní pro členy klubu. Hlavním cílem bylo zajistit, aby se jako "čekající" (s otazníkem) zobrazovali pouze hráči, u kterých je docházka skutečně vyžadována (nastavení `track_attendance` v profilu plátce).

## Technické změny

### 1. Filtrování očekávaných hráčů
- V `AttendanceController` a `DashboardController` byla upravena logika pro výpočet `expected_players_count`.
- Systém nyní identifikuje sezónu události a kontroluje tabulku `user_season_configs`.
- Hráč je považován za "očekávaného" pouze pokud:
    - Je aktivním členem týmu, kterému je událost určena.
    - Má pro danou sezónu nastaveno `track_attendance = true`.

### 2. Důvody omluvy
- Při odmítnutí účasti (`declined`) má nyní uživatel možnost vybrat konkrétní důvod (nemoc, zranění, práce, rodinné důvody atd.).
- Tento důvod se ukládá do pole `note` v tabulce `attendances` (případně se kombinuje s textovou poznámkou).
- Byly přidány nové lokalizační klíče do `lang/cs/member.php` a `lang/en/member.php`.

### 3. Vylepšení detailu události
- Šablona `member/attendance/show.blade.php` byla přepracována pro maximální přehlednost.
- Účastníci jsou rozděleni do 3 jasných sekcí:
    - **Přijdou:** Všichni s potvrzenou účastí.
    - **Omluvení:** Všichni s odmítnutou účastí (včetně zobrazení důvodu).
    - **Zatím neví:** Pouze "očekávaní" hráči, kteří se dosud nevyjádřili.
- Použit Alpine.js pro interaktivní RSVP formulář (dynamické zobrazení důvodu omluvy).

### 4. Responzivita a UI
- Úprava `event-card` komponenty pro lepší zobrazení statistik na mobilních zařízeních.
- Sjednocení barevného kódování statusů napříč členskou sekcí.

## Dopad na data
Změna je plně zpětně kompatibilní. Starší záznamy v poli `note` zůstávají zachovány, nové omluvy budou mít formát `Důvod (poznámka)`.
