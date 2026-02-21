# Veřejný frontend (Public Frontend)

Tato dokumentace popisuje strukturu a vizuální prvky veřejné části webu Kbelští sokoli.

## Stránka "V přípravě" (Under Construction)

Stránka se zobrazuje automaticky, pokud není publikován žádný obsah, nebo ji lze manuálně aktivovat v administraci (Branding Settings).

### Vizuální prvky
- **Téma:** Basketbalové hřiště s taktickými prvky (X a O).
- **Textura:** Na celou stránku je aplikován jemný "grainy" overlay pro prémiovější vzhled.
- **Interaktivita:** Animovaný basketbalový míč s CSS září a hover efekty.
- **Responzivita:** Stránka je plně optimalizována pro mobilní zařízení (využívá `overflow-y-auto` místo `hidden`, aby byl obsah dostupný i na malých obrazovkách).

### Technické detaily
- **Šablona:** `resources/views/public/under-construction.blade.php`.
- **Barvy:** Dynamicky přebírá barvy z `BrandingService`. Využívá CSS proměnné (např. `--color-primary`, `--color-primary-rgb`).
- **Písmo:** Využívá Google Fonts (Instrument Sans, Oswald, Permanent Marker).

## Branding Integrace

Barvy a základní informace (sociální sítě, kontakt) jsou spravovány přes `App\Services\BrandingService`. Ten poskytuje:
- Pole s nastavením pro Blade šablony.
- Vygenerovaný blok CSS proměnných pro `:root`.
- RGB varianty barev pro podporu opacity v CSS.
