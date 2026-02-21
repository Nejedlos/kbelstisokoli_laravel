# Administrace (Administration)

Administrace projektu je realizována pomocí **Filament PHP 5**.

## Hlavní moduly
1. **Dashboard:** Přehled důležitých informací (statistiky, grafy, notifikace).
2. **Uživatelé:** Správa členů a adminů.
3. **Ekonomika:** Platby a příspěvky.
4. **Obsah (CMS):** Správa webové prezentace (články, akce, galerie).

## Vlastní komponenty
Administrace bude využívat standardní Filament komponenty a také vlastní:
- Slider pro výběr hodnot (implementován v `public/js/filament/forms/components/slider.js`).
- Code editor pro technická nastavení.

## Přístup
Administrace je dostupná na `/admin` (podle výchozího nastavení Filamentu). Přístup je povolen pouze uživatelům s příslušnou rolí.
