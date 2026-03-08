# Úvod a přehled projektu

Vítejte v technické dokumentaci projektu Kbelští sokoli. Tato sekce poskytuje základní přehled o systému, jeho účelu a klíčových technologiích.

## Účel systému
Systém slouží pro komplexní správu klubu Kbelští sokoli, zahrnující:
- Členskou základnu a profily sportovců.
- Sportovní plánování, tréninky a docházku (Docházka).
- Ekonomickou agendu, členské příspěvky a fakturaci.
- Komunikaci s členy a veřejnou prezentaci klubu.

## Technologický stack
- **Backend:** Laravel 12 (PHP 8.4+)
- **Administrace:** Filament PHP 5
- **Frontend:** Laravel Folio, Blade, Livewire, Tailwind CSS
- **Databáze:** SQLite (vývoj), MySQL (produkce)

## Kompletní dokumentace
Tento soubor je součástí strukturované dokumentace. Kompletní přehled všech témat naleznete v hlavním rozcestníku:

👉 [**Index dokumentace (Rozcestník)**](../index.md)

## Rychlý start pro vývojáře
Projekt je plně kontejnerizován pomocí Laravel Sail.

```bash
# Spuštění prostředí
./vendor/bin/sail up -d

# Prvotní nastavení (migrace a seedování dat)
./vendor/bin/sail artisan migrate --seed

# Sestavení assetů
npm install && npm run build
```

Podrobné informace o konfiguraci naleznete v [Konfiguraci prostředí](./04-konfigurace.md).
