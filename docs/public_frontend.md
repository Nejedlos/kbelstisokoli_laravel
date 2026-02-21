# Veřejný frontend (Public Frontend)

Tato dokumentace popisuje strukturu a vizuální prvky veřejné části webu Kbelští sokoli.

## Stránka "V přípravě" (Under Construction)

Stránka se zobrazuje automaticky, pokud není publikován žádný obsah, nebo ji lze manuálně aktivovat v administraci (Branding Settings).

### Vizuální prvky
- **Téma:** Basketbalové hřiště s taktickými prvky (X a O).
- **Pozadí a hloubka:**
    - Odstraněna rušivá tečkovaná mřížka a nahrazena systémem tří velkých atmosférických gradientů (primární barva nahoře, doplňková modrá v rozích).
    - Taktické prvky jsou ztlumeny a překryty vertikálním gradientem pro plynulý zánik v okrajích.
    - Šum (grainy overlay) je implementován jako pseudoelement `body::before` s nízkou opacitou (2 %), což dodává pocit "materiálu" bez vizuálního šumu.
- **Tlačítka:** Hlavní akce (CTA) využívají plnou brandovou barvu s výrazným stínem pro maximální viditelnost.
- **Interaktivita:** Animovaný basketbalový míč s CSS září a hover efekty.
- **Responzivita:** Stránka je plně optimalizována pro mobilní zařízení.
- **Záře (Glow):** Využívá se pozadí s rozmazanou září (blur) kolem hlavního obsahu pro lepší hloubku.

### Technické detaily
- **Šablona:** `resources/views/public/under-construction.blade.php`.
- **Barvy:** Dynamicky přebírá barvy z `BrandingService`. Využívá CSS proměnné (např. `--color-primary`, `--color-primary-rgb`).
- **Písmo:** Využívá Google Fonts (Instrument Sans, Oswald, Permanent Marker).

## Branding Integrace

Barvy a základní informace (sociální sítě, kontakt) jsou spravovány přes `App\Services\BrandingService`. Ten poskytuje:
- Pole s nastavením pro Blade šablony.
- Vygenerovaný blok CSS proměnných pro `:root`.
- RGB varianty barev pro podporu opacity v CSS.
