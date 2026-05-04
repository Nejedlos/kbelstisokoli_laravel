# Změna: Podpora médií a příloh pro klubové akce

**Datum:** 4. května 2026
**Autor:** Junie

## Popis změn
Byla přidána možnost nahrávat plakáty (upoutávky) a libovolné přílohy (PDF, dokumenty) ke klubovým akcím v administraci. Tato média se následně automaticky zobrazují v detailu akce na frontendu.

### Backend (Model & Admin)
- Model `ClubEvent` nyní implementuje `HasMedia` (Spatie Media Library).
- Definována kolekce `poster` pro hlavní obrázek akce (s podporou responzivních obrázků a konverzí).
- Definována kolekce `attachments` pro ostatní soubory.
- Do Filament Resource (`ClubEventForm`) přidána sekce **Média a přílohy** s poli pro nahrávání (nastaveno na plnou šířku `columnSpanFull`).

### Frontend
- Upraven Blade pohled `public.events.show` pro zobrazení:
    - **Plakát:** Zobrazuje se jako velký obrázek nad popisem akce.
    - **Přílohy:** Zobrazují se pod popisem v mřížce s ikonami podle typu souboru (PDF, Word, Excel, atd.).
- Přidány lokalizační řetězce `events.attachments` a `events.no_attachments` (CS/EN).

## Technické detaily
- Kolekce `poster` využívá disk `media_public` a konverze `large` (1200px) a `thumb` (400px).
- Kolekce `attachments` využívá disk `media_public` bez konverzí.
- Ikony souborů jsou řešeny přes Font Awesome 7 Pro (Light varianta) s mapováním na příponu souboru.
