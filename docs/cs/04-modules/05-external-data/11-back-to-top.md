# Modul: Back to Top (Zpět nahoru)

Tento modul poskytuje moderní a elegantní tlačítko pro rychlý návrat na začátek stránky. Tlačítko má basketbalovou tematiku a je integrováno napříč celým projektem (frontend, administrace, členská sekce).

## Technická specifikace

- **Komponenta:** `resources/views/components/back-to-top.blade.php`
- **Technologie:** 
    - **Alpine.js:** Zajišťuje logiku zobrazení při skrolování a plynulý návrat nahoru.
    - **Tailwind CSS:** Definice vzhledu, animací a responzivity.
    - **Font Awesome 7 Pro (Light):** Ikona basketbalového míče (`fa-light fa-basketball`).

## Vlastnosti designu

- **Basketbalová rotace:** Při najetí myší (hover) se ikona basketbalového míče plynule otočí o 360 stupňů.
- **Moderní efekty:** 
    - Skleněný efekt (glassmorphism) v tooltipu.
    - Odlesk (shine effect) na pozadí tlačítka.
    - Animované pulzující kruhy (pulse rings) kolem tlačítka pro jemné upoutání pozornosti.
    - Plynulé přechody (transitions) pro vstup a výstup tlačítka na scénu.
- **Responzivita:** Tlačítko je fixně umístěno v pravém dolním rohu a přizpůsobuje se velikosti zařízení.

## Integrace

Tlačítko je do systému vloženo následovně:

1. **Veřejný web (Frontend):** Automaticky součástí komponenty `x-footer`.
2. **Administrace (Filament):** Registrováno v `AdminPanelProvider.php` pomocí render hooku `panels::body.end`.
3. **Členská sekce:** Vloženo přímo do layoutu `resources/views/layouts/member.blade.php`.

## Použití v kódu

Komponentu lze kdykoliv ručně vložit do jakéhokoliv Blade souboru:

```blade
<x-back-to-top />
```

Logika zobrazení: Tlačítko se automaticky objeví, jakmile uživatel odskroluje více než **500 pixelů** od horního okraje stránky.
