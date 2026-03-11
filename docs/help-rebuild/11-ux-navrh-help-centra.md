# UX Návrh nového Help Centra

Tento dokument definuje uživatelskou zkušenost (UX) a vizuální strukturu nového Help Centra projektu Kbelští sokoli.

## 1. Designové principy
- **Přehlednost:** Minimální clutter, jasná typografie, výrazné nadpisy.
- **Rychlost:** Okamžité vyhledávání (Livewire), rychlá navigace mezi sekcemi.
- **Kontext:** Uživatel vždy ví, kde se nachází (breadcrumbs) a pro koho je obsah určen (audience badge).
- **Branding:** Využití klubových barev (modrá, oranžová), Font Awesome 7 Light ikon a Glassmorphism prvků.

## 2. Hlavní stránka (Help Home)
Cílem je rychle nasměrovat uživatele do správné sekce nebo mu umožnit okamžitě vyhledávat.

### Prvky:
- **Hero Sekce:**
    - Velký vyhledávací panel s placeholderem "Jak vám můžeme pomoci?".
    - Podnadpis definující účel nápovědy.
- **Karty kategorií (Grid):**
    - 6 hlavních karet (Úvod, Sport, Lidé, Finance, Obsah, Systém).
    - Každá karta obsahuje: Ikonu, Název, Krátký popis, Počet článků.
    - Hover efekt: Mírné zvětšení a zvýraznění barvy kategorie.
- **Globální Rychlé akce (Sidebar nebo Spodní sekce):**
    - Odkazy na nejčastější úkony (např. "Vytvořit trénink", "Přidat člena").
- **Populární FAQ:**
    - Seznam 3-5 nejčastějších dotazů napříč systémem.

## 3. Listing kategorie
Zobrazuje články v konkrétní sekci.

### Prvky:
- **Header:**
    - Breadcrumbs (Domů > Sport).
    - Název kategorie s ikonou.
    - Popis sekce a pro koho je primárně určena.
- **Seznam článků:**
    - Karty článků obsahující: Titulek, krátké shrnutí (z metadat), cílové role (badge).
- **Sidebar:**
    - Přepínač na ostatní kategorie pro rychlý přechod.

## 4. Detail článku
Nejdůležitější část systému, kde dochází k samotné konzumaci informací.

### Prvky:
- **Main Content (Střed):**
    - Breadcrumbs (Domů > Sport > Docházka).
    - Titulek (H1).
    - Metadata blok: Účel článku, Cílové publikum (např. Trenér, Hospodář).
    - Markdown tělo: Čistá typografie, podpora pro callouty (Tip, Varování, Chyba).
    - FAQ sekce: Harmonika s dotazy specifickými pro tento článek.
- **Right Sidebar (Navigace a kontext):**
    - **Obsah (On this page):** Kotvy na H2/H3 v textu. Sticky při scrollu.
    - **Rychlé akce:** Tlačítka s přímými linky do administrace související s článkem.
    - **Související články:** Odkazy na další relevantní témata.
- **Footer článku:**
    - Hodnocení užitečnosti (Thumbs up/down).
    - Poslední aktualizace.
    - Kontakt na podporu.

## 5. Vyhledávání (Search UX)
- **Real-time:** Výsledky se objevují během psaní.
- **Kategorizace:** Výsledky rozdělené podle kategorií pro lepší orientaci.
- **Zvýraznění:** Highlight hledaného termínu v úryvku textu.
- **Empty State:** Návrh alternativních témat, pokud není nic nalezeno.

## 6. Vizuální komponenty (UI Kit)
- **Callouty:**
    - `TIP`: Modrý background, žárovka.
    - `VAROVÁNÍ`: Oranžový background, vykřičník.
    - `POZOR/CHYBA`: Červený background, křížek.
- **Badges:** Pro role (Trenér = zelená, Admin = červená, atd.).
- **Breadcrumbs:** Decentní text s oddělovači, plně klikatelné.
- **Tlačítka rychlých akcí:** Outline styl s ikonou, primární barva kategorie.

## 7. Responsivita
- **Desktop:** Třísloupcové rozložení (Navigace | Obsah | Sidebar).
- **Tablet:** Dvousloupcové rozložení (Obsah | Sidebar pod ním).
- **Mobile:** Jednosloupcové, navigace v off-canvas menu nebo collapsible sekci nahoře.

## 8. Přístupnost (A11y)
- Kontrastní barvy splňující WCAG AA.
- Správná sémantika (H1-H6, `<article>`, `<nav>`).
- Focus stavy pro klávesnici.
- Aria-labels pro interaktivní prvky bez textu.
