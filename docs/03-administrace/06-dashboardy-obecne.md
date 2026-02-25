# Dashboardy: Responzivita a Feedback (Member vs Admin)

Tento dokument popisuje pravidla pro responzivitu dashboardů a kontextové umístění zpětné vazby (kontaktů) v sekci Member a v administraci (Filament). Cílem je:
- sjednotit chování tlačítek na mobilu (100% šířka),
- vést členy primárně na trenéry,
- v administraci (kde pracují mimo jiné trenéři) zajistit snadný kontakt na administrátora.

## 1. Responzivita tlačítek
- Mobile-first: na úzkých obrazovkách mají akční prvky plnou šířku.
  - Využívejte `w-full sm:w-auto` u odkazů/tlačítek: `class="btn ... w-full sm:w-auto"`.
  - Kontejnery akčních prvků skládejte do sloupců na mobilu: `flex flex-col sm:flex-row ...` nebo `grid grid-cols-1 sm:grid-cols-...`.
- Gridy KPI a karet:
  - Používejte `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3` (dle potřeby).
- Filament widgety:
  - V šablonách widgetů používejte u akčních tlačítek stejné pravidlo `w-full sm:w-auto` a na kontejnerech `flex flex-col sm:flex-row`.

## 2. Kontextová zpětná vazba (Member sekce)
- Zásada: členové řeší primárně věci s trenérem svého týmu. Admin je až poslední možnost.
- Umístění na dashboardu:
  - V sekci Economy (platby) zobrazte nenásilný blok s CTA „Kontaktovat trenéra“ a sekundárním odkazem na admina.
  - V dalších kontextových sekcích (docházka, týmové přehledy) budeme obdobně směřovat na trenéry – viz TODO níže.

## 3. Kontextová zpětná vazba (Admin/Filament)
- Trenéři v administraci potřebují rychlou linku na admina:
  - Na admin dashboard přidejte widget „Contact Admin“ s:
    - shrnutím kontaktu (jméno, e‑mail, telefon),
    - primární CTA na formulář („Napsat administrátorovi“),
    - volitelně sekundární `mailto:`.
- Ikony: použijte Font Awesome Light (`fa-light`) přímo v Blade šablonách (HtmlString nebo přímo v `<i>`).

## 4. Lokalizace
- Nové texty jsou přidány do:
  - `lang/cs/member.php` a `lang/en/member.php` → `feedback.hints.economy`.
  - `lang/cs/admin/dashboard.php` a `lang/en/admin/dashboard.php` → `contact_admin.*`.

## 5. TODO – rozšíření na další stránky
- Postupně doplňovat chytrou zpětnou vazbu na:
  - Stránky docházky → odkaz na trenéra(y) týmu.
  - Detail týmu a související reporty → odkaz na trenéry.
  - Ekonomika – detail předpisu → kontextový odkaz na trenéra příslušného týmu.
- Při doplňování zachovat zásadu: member → primárně trenér, admin → admin.

## 6. Build a verifikace
- Po změnách v CSS/JS spusťte `npm run build` (kvůli Vite manifestu).
- Pro kontrolu vzhledu použijte snapshot HTML (viz `docs/08-manualy-a-ostatni/01-renderovani-html.md`).

---
Aktualizace: 2026‑02‑24
